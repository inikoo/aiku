<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Nov 2025 13:05:00 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use Illuminate\Support\Arr;
use App\Actions\Chat\ChatSession\AssignChatToAgent;
use App\Actions\Chat\ChatSession\CloseChatSession;
use App\Actions\Chat\ChatSession\GetChatCustomerProfile;
use App\Actions\Chat\ChatSession\GetChatSessions;
use App\Actions\Chat\ChatSession\SendChatMessage;
use App\Actions\Chat\ChatSession\StoreChatSession;
use App\Actions\Chat\ChatSession\SummarizeChatSession;
use App\Actions\Chat\ChatSession\TranslateChatMessage;
use App\Models\Helpers\Language;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Enums\CRM\Livechat\ChatMessageTypeEnum;
use App\Enums\CRM\Livechat\ChatPriorityEnum;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Enums\CRM\Livechat\ChatTopicEnum;
use App\Enums\CRM\Livechat\ChatChannelEnum;
use App\Enums\CRM\Livechat\ChatSessionStatusEnum;
use App\Models\Chat\ChatAgent;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatSession;
use App\Models\Chat\MetaChannel;
use App\Models\Chat\MetaChatSession;
use App\Models\Catalogue\Product;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\SysAdmin\User;
use App\Models\Web\Website;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

require_once __DIR__.'/../Support/ChatHelpers.php';

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $web = Website::has('shop')->first();
    if (!$web) {
        list(
            $this->organisation,
            $this->user,
            $this->shop
        ) = createShop();
        $web = createWebsite($this->shop);
    } else {
        $this->organisation = $web->organisation;
        $this->user         = createAdminGuest($this->organisation->group)->getUser();
        $this->shop         = $web->shop;
    }
    $web->refresh();
    $this->web       = $web;
    $this->warehouse = createWarehouse();

    $this->customer = createCustomer($this->shop);

    $this->action               = new StoreChatSession();
    $this->sendMessageAction    = new SendChatMessage();
    $this->assignmentChatAction = new AssignChatToAgent();
    $this->closeChatAction      = new CloseChatSession();

    \Illuminate\Support\Facades\Config::set('inertia.testing.page_paths', [resource_path('js/Pages/Grp')]);
});

test('SummarizeChatSession classifies what the customer wanted and leaves system messages out', function () {
    $webUser = StoreWebUser::make()->action($this->customer, WebUser::factory()->definition());
    config(['askbot-laravel.openai_api_key' => 'test-key']);

    \Illuminate\Support\Facades\Http::fake([
        'api.openai.com/*' => \Illuminate\Support\Facades\Http::response(['choices' => [['message' => [
            'content' => "```json\n".json_encode([
                'summary'    => 'Two candles missing from GB589048, replacement sent.',
                'topic'      => 'missing_or_damaged',
                'key_points' => ['Two candles missing'],
                'status'     => 'resolved',
                'sentiment'  => 'neutral',
            ])."\n```",
        ]]]]),
    ]);

    $chatSession = ChatSession::create([
        'ulid'             => (string) Str::ulid(),
        'status'           => ChatSessionStatusEnum::CLOSED,
        'guest_identifier' => 'guest_'.Str::random(5),
        'language_id'      => 68,
        'priority'         => ChatPriorityEnum::NORMAL,
        'shop_id'          => $this->shop->id,
        'web_user_id'      => $webUser->id,
    ]);

    foreach ([
        [ChatSenderTypeEnum::USER, 'Two candles are missing from GB589048'],
        [ChatSenderTypeEnum::SYSTEM, 'Chat session has been closed by agent'],
    ] as [$senderType, $text]) {
        ChatMessage::create([
            'chat_session_id' => $chatSession->id,
            'message_type'    => ChatMessageTypeEnum::TEXT,
            'sender_type'     => $senderType,
            'message_text'    => $text,
        ]);
    }

    $chatSession = SummarizeChatSession::make()->handle($chatSession)->refresh();

    expect($chatSession->topic)->toBe(ChatTopicEnum::MISSING_OR_DAMAGED->value)
        ->and($chatSession->summarised_at)->not->toBeNull()
        ->and(Arr::get($chatSession->metadata, 'ai_summary.summary'))->toContain('GB589048')
        ->and(Arr::get($chatSession->metadata, 'ai_summary.model'))->toBe(config('chat.summary_writer_model'))
        ->and(Arr::get($chatSession->metadata, 'ai_summary'))->not->toHaveKey('topic');

    \Illuminate\Support\Facades\Http::assertSent(
        fn ($request) => str_contains($request['messages'][1]['content'], 'customer: Two candles')
            && !str_contains($request['messages'][1]['content'], 'closed by agent')
    );

    $previousContact = GetChatCustomerProfile::make()->previousContact($this->customer, new ChatSession());

    expect($previousContact['previous_chats'][0]['ulid'])->toBe($chatSession->ulid)
        ->and($previousContact['previous_chats'][0]['summary'])->toContain('GB589048')
        ->and($previousContact['chat_topics'][0])->toMatchArray(['topic' => 'missing_or_damaged', 'count' => 1])
        ->and(GetChatCustomerProfile::make()->previousContact($this->customer, $chatSession)['previous_chats'])->toBeEmpty();
});

test('SummarizeChatSession does not ask the model about a conversation the customer never wrote in', function () {
    \Illuminate\Support\Facades\Http::fake();

    $chatSession = ChatSession::create([
        'ulid'             => (string) Str::ulid(),
        'status'           => ChatSessionStatusEnum::CLOSED,
        'guest_identifier' => 'guest_'.Str::random(5),
        'language_id'      => 68,
        'priority'         => ChatPriorityEnum::NORMAL,
        'shop_id'          => $this->shop->id,
    ]);

    ChatMessage::create([
        'chat_session_id' => $chatSession->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => ChatSenderTypeEnum::AGENT,
        'message_text'    => 'Hello, can I help?',
    ]);

    $chatSession = SummarizeChatSession::make()->handle($chatSession)->refresh();

    \Illuminate\Support\Facades\Http::assertNothingSent();
    expect($chatSession->topic)->toBeNull()->and($chatSession->summarised_at)->toBeNull();
});

test('a long email gets a short summary beside it and a short one is left alone', function () {
    \Illuminate\Support\Facades\Http::fake();

    $long  = noiseTestEmailSession($this->shop, 'long@example.com', 'Order 1234', 'Please send order 1234 to 5 High St. '.str_repeat('Kind regards and our company disclaimer. ', 30))->messages()->first();
    $short = noiseTestEmailSession($this->shop, 'short@example.com', 'Order 99', 'Where is order 99?')->messages()->first();

    \App\Actions\Helpers\AI\AskToAi::shouldRun()->once()->andReturn('{"summary": "Wants order 1234 sent to 5 High St."}');

    expect(\App\Actions\Chat\ChatSession\SummarizeLongEmail::run($long))->toBe('Wants order 1234 sent to 5 High St.')
        ->and(\App\Actions\Chat\ChatSession\SummarizeLongEmail::run($short))->toBeNull()
        ->and(data_get($long->refresh()->metadata, 'ai_summary'))->toBe('Wants order 1234 sent to 5 High St.')
        ->and($long->message_text)->toStartWith('Please send order 1234');
});

test('a fact drawer opens only what is on the menu and only for the customer who is writing', function () {
    $customer = createOwnCustomer($this->shop, 'fact-drawers');
    $drawer   = \App\Actions\Chat\ChatSession\OpenChatFactDrawer::make();
    $facts    = ['order_facts' => ['order' => ['reference' => 'NOT-THEIRS-1']]];

    expect($drawer->handle('delete_everything', $this->shop, $customer, $facts))->toBeNull()
        ->and($drawer->handle('order_lines', $this->shop, $customer, $facts))->toBeNull()
        ->and($drawer->handle('order_payment', $this->shop, null, $facts))->toBeNull()
        ->and($drawer->handle('replacements', $this->shop, null, []))->toBeNull()
        ->and($drawer->handle('alternatives', $this->shop, $customer, []))->toBeNull()
        ->and($drawer->handle('shop_policies', $this->shop, null, []))->toBeNull();

    \App\Actions\Chat\UpdateShopChatPolicies::make()->handle($this->shop, ['policies' => 'Minimum first order: 100 EUR']);
    expect($drawer->handle('shop_policies', $this->shop->refresh(), null, []))->toBe(['text' => 'Minimum first order: 100 EUR']);
    \App\Actions\Chat\UpdateShopChatPolicies::make()->handle($this->shop, ['policies' => '']);
});

test('a claim is prepared for the agent: the order the customer names, and a replacement reason from what they wrote', function () {
    $customer  = createOwnCustomer($this->shop, 'claim-case');
    $webUser   = \App\Actions\CRM\WebUser\StoreWebUser::make()->action($customer, WebUser::factory()->definition());
    $reference = 'CLM'.random_int(100000, 999999);

    \Illuminate\Support\Facades\DB::table('orders')->insert([
        'group_id'        => $this->shop->group_id,
        'organisation_id' => $this->shop->organisation_id,
        'shop_id'         => $this->shop->id,
        'customer_id'     => $customer->id,
        'currency_id'     => $this->shop->currency_id,
        'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id,
        'slug'            => 'ord-'.uniqid(),
        'reference'       => $reference,
        'state'           => 'dispatched',
        'net_amount'      => 100,
        'org_net_amount'  => 100,
        'grp_net_amount'  => 100,
        'status'          => \App\Enums\Ordering\Order\OrderStatusEnum::SETTLED,
        'payment_data'    => '{}',
        'data'            => '{}',
        'date'            => now()->subDays(3),
        'dispatched_at'   => now()->subDays(2),
        'created_at'      => now()->subDays(3),
        'updated_at'      => now()->subDays(2),
    ]);

    $session = ChatSession::create([
        'ulid'        => (string) Str::ulid(),
        'status'      => ChatSessionStatusEnum::WAITING,
        'channel'     => ChatChannelEnum::WEBSITE,
        'shop_id'     => $this->shop->id,
        'web_user_id' => $webUser->id,
        'topic'       => \App\Enums\CRM\Livechat\ChatTopicEnum::MISSING_OR_DAMAGED->value,
    ]);
    ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => ChatSenderTypeEnum::USER,
        'message_text'    => "Order $reference arrived and two candles are broken",
    ]);

    $claim = \App\Actions\Chat\ChatSession\GetChatClaimCase::run($session, $customer);

    expect($claim['is_claim'])->toBeTrue()
        ->and($claim['order']['reference'])->toBe($reference)
        ->and($claim['order']['named'])->toBeTrue()
        ->and($claim['reason'])->toBe('damaged_in_transit')
        ->and($claim['replacement']['name'])->toBe('grp.models.order.replacement_delivery_note.store');
});

test('a claim with no order number falls back to the last dispatched order, and the agent can pick an earlier one', function () {
    $customer = createOwnCustomer($this->shop, 'claim-pick');
    $webUser  = \App\Actions\CRM\WebUser\StoreWebUser::make()->action($customer, WebUser::factory()->definition());

    $insertOrder = function (string $reference, int $dispatchedDaysAgo) use ($customer) {
        \Illuminate\Support\Facades\DB::table('orders')->insert([
            'group_id'        => $this->shop->group_id,
            'organisation_id' => $this->shop->organisation_id,
            'shop_id'         => $this->shop->id,
            'customer_id'     => $customer->id,
            'currency_id'     => $this->shop->currency_id,
            'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id,
            'slug'            => 'ord-'.uniqid(),
            'reference'       => $reference,
            'state'           => 'dispatched',
            'net_amount'      => 100,
            'org_net_amount'  => 100,
            'grp_net_amount'  => 100,
            'status'          => \App\Enums\Ordering\Order\OrderStatusEnum::SETTLED,
            'payment_data'    => '{}',
            'data'            => '{}',
            'date'            => now()->subDays($dispatchedDaysAgo + 1),
            'dispatched_at'   => now()->subDays($dispatchedDaysAgo),
            'created_at'      => now()->subDays($dispatchedDaysAgo + 1),
            'updated_at'      => now()->subDays($dispatchedDaysAgo),
        ]);
    };

    $earlier = 'CLE'.random_int(100000, 999999);
    $latest  = 'CLL'.random_int(100000, 999999);
    $insertOrder($earlier, 6);
    $insertOrder($latest, 4);

    $session = ChatSession::create([
        'ulid'        => (string) Str::ulid(),
        'status'      => ChatSessionStatusEnum::WAITING,
        'channel'     => ChatChannelEnum::WEBSITE,
        'shop_id'     => $this->shop->id,
        'web_user_id' => $webUser->id,
        'topic'       => \App\Enums\CRM\Livechat\ChatTopicEnum::MISSING_OR_DAMAGED->value,
    ]);
    ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => ChatSenderTypeEnum::USER,
        'message_text'    => 'The three large glass bowls are broken',
    ]);

    $fallback = \App\Actions\Chat\ChatSession\GetChatClaimCase::run($session, $customer);
    $picked   = \App\Actions\Chat\ChatSession\GetChatClaimCase::run($session, $customer, $earlier);
    $foreign  = \App\Actions\Chat\ChatSession\GetChatClaimCase::run($session, $customer, 'NOT-THEIRS');

    expect($fallback['order']['reference'])->toBe($latest)
        ->and($fallback['order']['named'])->toBeFalse()
        ->and($fallback['orders'])->toBe([$latest, $earlier])
        ->and($picked['order']['reference'])->toBe($earlier)
        ->and($picked['order']['named'])->toBeTrue()
        ->and($foreign['order']['reference'])->toBe($latest);
});

test('a question about an order gets a draft written from that customer\'s order, and what staff do with it is counted', function () {
    config(['chat.ai_drafts' => true, 'askbot-laravel.openai_api_key' => 'test-key']);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class]);

    $customer  = createOwnCustomer($this->shop, 'ai-draft-orders');
    $webUser   = \App\Actions\CRM\WebUser\StoreWebUser::make()->action($customer, WebUser::factory()->definition());
    $reference = 'AID'.random_int(100000, 999999);

    \Illuminate\Support\Facades\DB::table('orders')->insert([
        'group_id'        => $this->shop->group_id,
        'organisation_id' => $this->shop->organisation_id,
        'shop_id'         => $this->shop->id,
        'customer_id'     => $customer->id,
        'currency_id'     => $this->shop->currency_id,
        'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id,
        'slug'            => 'ord-'.uniqid(),
        'reference'       => $reference,
        'state'           => 'dispatched',
        'net_amount'      => 100,
        'org_net_amount'  => 100,
        'grp_net_amount'  => 100,
        'status'          => \App\Enums\Ordering\Order\OrderStatusEnum::SETTLED,
        'payment_data'    => '{}',
        'data'            => '{}',
        'date'            => '2026-09-21',
        'submitted_at'    => '2026-09-21 10:00:00',
        'dispatched_at'   => '2026-09-22 15:00:00',
        'created_at'      => '2026-09-21',
        'updated_at'      => '2026-09-22',
    ]);

    $asks        = null;
    $objection   = null;
    $modelAnswer = ['answerable' => true, 'topic' => 'order_status', 'reply' => "Hi, your order $reference was dispatched on 22 September."];
    fakeDraftLanguageDetection();
    \Illuminate\Support\Facades\Http::fake([
        'api.openai.com/*' => function ($request) use (&$modelAnswer, &$asks, &$objection) {
            if ($objection !== null && str_contains((string) data_get($request->data(), 'messages.1.content'), '{"objection":')) {
                return \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => json_encode(['objection' => $objection, 'send' => false])]]]]);
            }

            if ($asks && str_contains((string) data_get($request->data(), 'messages.1.content'), '{"asks":')) {
                return \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => json_encode(['asks' => $asks])]]]]);
            }

            $copiedExample = preg_match('/\{"answerable".*\}/', (string) data_get($request->data(), 'messages.1.content'), $example) ? $example[0] : '';

            return aiDraftTestModel($request, $modelAnswer === null ? $copiedExample : json_encode($modelAnswer));
        },
    ]);

    $session = ChatSession::create([
        'ulid'                    => (string) Str::ulid(),
        'status'                  => ChatSessionStatusEnum::ACTIVE,
        'channel'                 => ChatChannelEnum::WEBSITE,
        'shop_id'                 => $this->shop->id,
        'web_user_id'             => $webUser->id,
        'last_visitor_message_at' => now(),
    ]);
    $ask = fn (ChatSession $session, string $text, ChatSenderTypeEnum $as = ChatSenderTypeEnum::USER) => ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => $as,
        'message_text'    => $text,
    ]);

    $ask($session, 'Hello, where is my order please? I have not received it yet');
    $draft = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);

    expect($draft->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::PENDING)
        ->and($draft->topic)->toBe(\App\Enums\CRM\Livechat\ChatTopicEnum::ORDER_STATUS)
        ->and($draft->facts['order_facts']['order']['reference'])->toBe($reference)
        ->and($draft->facts['order_facts']['order']['dispatched_on'])->toBe('2026-09-22');

    $email = ChatSession::create([
        'ulid'                    => (string) Str::ulid(),
        'status'                  => ChatSessionStatusEnum::ACTIVE,
        'channel'                 => ChatChannelEnum::EMAIL,
        'shop_id'                 => $this->shop->id,
        'web_user_id'             => $webUser->id,
        'last_visitor_message_at' => now(),
    ]);
    $ask($email, 'Hello, where is my order please? I have not received it yet');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($email))->toBeNull();

    // The model is handed the facts, and only this customer's: the order is in what it was sent.
    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => str_contains($request->body(), $reference));

    // A stranger asking the same thing gets nothing from any customer's orders, so no draft at all.
    $stranger = ChatSession::create([
        'ulid'    => (string) Str::ulid(),
        'status'  => ChatSessionStatusEnum::WAITING,
        'channel' => ChatChannelEnum::WEBSITE,
        'shop_id' => $this->shop->id,
    ]);
    $ask($stranger, "Where is my order $reference please?", ChatSenderTypeEnum::GUEST);
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($stranger))->toBeNull();

    // The model is told the language of what the customer wrote: left to match the customer, it
    // answered an English customer in Spanish.
    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => str_contains($request->body(), 'Write in English,'));
    $spanish = $ask($stranger, 'Hola, ¿dónde está mi pedido?', ChatSenderTypeEnum::GUEST);
    $spanish->update(['original_language_id' => Language::where('code', 'es')->value('id')]);
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::replyLanguage($stranger, $spanish->refresh(), 'Hola, ¿dónde está mi pedido?')->name)->toBe('Spanish');

    actingAs($this->user);
    $shown = $this->getJson(route('grp.api.chats.sessions.ai_draft.show', [$session->ulid]))->assertOk()->json('data');
    expect($shown['id'])->toBe($draft->id)->and($shown['text'])->toBe($draft->text);

    $this->postJson(route('grp.api.chats.ai_drafts.take', [$draft->id]))->assertOk()->assertJsonPath('data.text', $draft->text);

    $this->postJson(route('grp.api.chats.ai_drafts.rate', [$draft->id]), ['rating' => 2, 'reason' => 'wrong_fact'])->assertOk();
    expect($draft->refresh()->only(['rating', 'rating_reason', 'rated_by_user_id']))->toBe(['rating' => 2, 'rating_reason' => 'wrong_fact', 'rated_by_user_id' => $this->user->id]);
    $this->postJson(route('grp.api.chats.ai_drafts.rate', [$draft->id]), ['rating' => 5, 'reason' => 'wrong_fact'])->assertOk();
    expect($draft->refresh()->only(['rating', 'rating_reason']))->toBe(['rating' => 5, 'rating_reason' => null]);
    $this->postJson(route('grp.api.chats.ai_drafts.rate', [$draft->id]), ['rating' => 6])->assertUnprocessable();

    $agent = ChatAgent::where('user_id', $this->user->id)->firstOrFail();
    $reply = $ask($session, "  Hi, your order $reference was dispatched on 22 September. ", ChatSenderTypeEnum::AGENT);
    $reply->update(['sender_id' => $agent->id]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($session, $reply);

    expect($draft->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED)
        ->and($draft->decided_by_user_id)->toBe($this->user->id);

    // A new question gets a new draft; the agent answers in their own words, so it was not used.
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, "Thanks. And is it coming with DPD or Royal Mail?");
    $second = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    $mine   = $ask($session, 'It is with APC, the tracking came by email.', ChatSenderTypeEnum::AGENT);
    $mine->update(['sender_id' => $agent->id]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($session, $mine);

    expect($second->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::SUPERSEDED);

    // An agent who copies the draft word for word without pressing Use still sent it as written.
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Sorry, where is my order again?');
    $copied = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    $pasted = $ask($session, $copied->text, ChatSenderTypeEnum::AGENT);
    $pasted->update(['sender_id' => $agent->id]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($session, $pasted);

    expect($copied->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED);

    expect(\App\Actions\Chat\ChatSession\SettleChatAiDraft::draftWordsKept('Please enter Ancient Wisdom Marketing Ltd as the warehouse name in TikTok Shop.', 'Hi, if you use Ancient Wisdom Marketing Ltd as the warehouse name in TikTok Shop.'))->toBeGreaterThan(0.6)->toBeLessThan(0.9)
        ->and(\App\Actions\Chat\ChatSession\SettleChatAiDraft::draftWordsKept('Your order [[which order?]] ships today.', 'Hello Ann, your order ships today. Carmen'))->toBe(1.0)
        ->and(\App\Actions\Chat\ChatSession\SettleChatAiDraft::draftWordsKept('Your order ships today.', 'It is with APC.'))->toBe(0.0);

    // Retyped with their own greeting and name, without pressing Use: still sent as written.
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Hi again, where is my order?');
    $retyped = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    $typed   = $ask($session, 'Hello! '.$retyped->text.' Carmen', ChatSenderTypeEnum::AGENT);
    $typed->update(['sender_id' => $agent->id]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($session, $typed);

    expect($retyped->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED);

    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Where is my order, please?');
    $replaced = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    $this->travel(1)->minutes();
    $ask($session, 'Hello? Where is my order?');
    $newer = \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    $newer->update(['text' => 'Sorry for the wait, we are checking with the courier now.']);
    $sentReplaced = $ask($session, $replaced->text, ChatSenderTypeEnum::AGENT);
    $sentReplaced->update(['sender_id' => $agent->id]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($session, $sentReplaced);

    expect($replaced->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED)
        ->and($replaced->reply_message_id)->toBe($sentReplaced->id)
        ->and($newer->refresh()->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::SUPERSEDED);

    // Answering a question we asked them ("yes", "please") gets no draft: what they want is in our question.
    $this->travel(1)->minutes();
    $ourQuestion = $ask($session, 'Do you mean the orders still with us?', ChatSenderTypeEnum::AGENT);
    $ourQuestion->update(['sender_id' => $agent->id]);
    $session->update(['last_agent_message_at' => now()]);
    $ask($session, 'yes please');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull()
        ->and(\App\Actions\Chat\ChatSession\DraftChatReply::answersOurQuestion('Do you mean the orders still with us?', 'yes, where is it?'))->toBeFalse();

    // When the facts do not answer the question the model says so, and there is no draft.
    $modelAnswer = ['answerable' => false];
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Can I change the delivery address of my next order?');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull();

    // Whether the facts answer it is decided before the model sees them: shown an order, it
    // answered where the order is when the customer asked for an alternative product.
    $modelAnswer = ['answerable' => true, 'topic' => 'order_status', 'reply' => "Hi, your order $reference was dispatched on 22 September."];
    $asks        = 'other';
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, "Order $reference: can you suggest something similar to the candles that are out of stock?");
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull();
    $asks = null;

    // A second model reviews every draft looking for a reason not to send it; one objection and
    // there is no draft, however good the first model thought it was.
    $objection   = 'It gives the status but the customer asks when it ships.';
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Where is my order please?');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull();
    $objection = null;

    // A model that copies the example answer in its instructions word for word gives no draft:
    // the placeholder names no order or product aiku looked up, and staff never see "the reply".
    // Real answers above still make drafts, which is what broke when the example held both topics.
    $modelAnswer = null;
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Where is my order now?');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull();

    // The reply is checked as well: one that comes back in another language than the customer's
    // is dropped, whatever the prompt said.
    $modelAnswer = ['answerable' => true, 'topic' => 'order_status', 'reply' => "Hola, tu pedido $reference salió el 22 de septiembre."];
    $session->update(['last_agent_message_at' => now()]);
    $this->travel(1)->minutes();
    $ask($session, 'Where is my order now please?');
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session))->toBeNull();

    // A draft is kept only when it names what aiku looked up: the first real one answered a
    // stock question about products aiku had no facts on, repeating the customer back as fact.
    $grounded = fn (string $topic, string $reply, array $facts) => \App\Actions\Chat\ChatSession\DraftChatReply::isGrounded(\App\Enums\CRM\Livechat\ChatTopicEnum::from($topic), $reply, $facts);
    $orderFacts = ['order_facts' => ['order' => ['reference' => $reference]]];
    expect($grounded('stock_availability', 'The ritual candles are out of stock, more is on order.', $orderFacts))->toBeFalse()
        ->and($grounded('stock_availability', 'MMC-01 is out of stock.', ['product_facts' => [['code' => 'MMC-02']]]))->toBeFalse()
        ->and($grounded('stock_availability', 'mmc-02 is out of stock, more is on order.', ['product_facts' => [['code' => 'MMC-02']]]))->toBeTrue()
        ->and($grounded('order_status', 'Your order is on its way.', $orderFacts))->toBeFalse()
        ->and($grounded('order_status', "Your order $reference is on its way.", $orderFacts))->toBeTrue();

    $stats = get(route('grp.chat.ai.dashboard'))->assertOk()->viewData('page')['props']['draftStats'];
    expect($stats['used'])->toBeGreaterThanOrEqual(1)->and($stats['superseded'])->toBeGreaterThanOrEqual(1);

    foreach ([$session, $stranger] as $each) {
        \App\Models\Chat\ChatAiDraft::where('chat_session_id', $each->id)->delete();
        $each->messages()->withTrashed()->forceDelete();
        $each->forceDelete();
    }
});

test('a draft goes to the customer without staff only out of hours, only once earned, and a flag closes it again', function () {
    config([
        'chat.ai_drafts'                   => true,
        'chat.ai_auto_send.enabled'        => true,
        'chat.ai_auto_send.min_decided'    => 3,
        'askbot-laravel.openai_api_key'    => 'test-key',
    ]);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class]);
    $schedule = outOfHoursTestSchedule($this->shop);

    $customer  = createOwnCustomer($this->shop, 'ai-auto-send');
    $webUser   = \App\Actions\CRM\WebUser\StoreWebUser::make()->action($customer, WebUser::factory()->definition());
    $reference = 'AAS'.random_int(100000, 999999);

    \Illuminate\Support\Facades\DB::table('orders')->insert([
        'group_id'        => $this->shop->group_id,
        'organisation_id' => $this->shop->organisation_id,
        'shop_id'         => $this->shop->id,
        'customer_id'     => $customer->id,
        'currency_id'     => $this->shop->currency_id,
        'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id,
        'slug'            => 'ord-'.uniqid(),
        'reference'       => $reference,
        'state'           => 'packed',
        'net_amount'      => 100,
        'org_net_amount'  => 100,
        'grp_net_amount'  => 100,
        'status'          => \App\Enums\Ordering\Order\OrderStatusEnum::CREATING,
        'payment_data'    => '{}',
        'data'            => '{}',
        'date'            => '2026-09-24',
        'submitted_at'    => '2026-09-24 09:00:00',
        'created_at'      => '2026-09-24',
        'updated_at'      => '2026-09-24',
    ]);

    fakeDraftLanguageDetection();
    \Illuminate\Support\Facades\Http::fake([
        'api.openai.com/*' => fn ($request) => aiDraftTestModel($request, json_encode([
            'answerable' => true, 'topic' => 'order_status', 'reply' => "Your order $reference is packed and waiting for the courier.",
        ])),
    ]);

    $session = ChatSession::create([
        'ulid'                    => (string) Str::ulid(),
        'status'                  => ChatSessionStatusEnum::ACTIVE,
        'channel'                 => ChatChannelEnum::WEBSITE,
        'shop_id'                 => $this->shop->id,
        'web_user_id'             => $webUser->id,
        'last_visitor_message_at' => now(),
    ]);
    $askAt = function (string $when, string $text) use ($session) {
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse($when, 'Europe/London'));
        $session->update(['last_agent_message_at' => now()->subHours(2)]);
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'message_type'    => ChatMessageTypeEnum::TEXT,
            'sender_type'     => ChatSenderTypeEnum::USER,
            'message_text'    => $text,
        ]);

        return \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    };
    $systemMessages = fn () => $session->messages()->where('sender_type', ChatSenderTypeEnum::SYSTEM)->count();

    // Out of hours, but nothing earned yet: the draft waits for staff.
    $waiting = $askAt('2026-09-26 11:00', 'Where is my order please?');
    expect($waiting->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::PENDING)->and($systemMessages())->toBe(0);

    // Staff sent three drafts on this topic exactly as written: now it is earned.
    foreach (range(1, 3) as $i) {
        \App\Models\Chat\ChatAiDraft::create([
            'group_id' => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id,
            'chat_session_id' => $session->id, 'topic' => 'order_status', 'facts' => [], 'text' => 'earned '.$i,
            'status' => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED,
        ]);
    }
    expect(\App\Actions\Chat\ChatSession\GetChatAutoSendGate::run($this->shop, \App\Enums\CRM\Livechat\ChatTopicEnum::ORDER_STATUS)['earned'])->toBeTrue();

    $sent = $askAt('2026-09-26 11:30', 'Is my order on its way?');
    $answer = $session->messages()->where('sender_type', ChatSenderTypeEnum::SYSTEM)->latest('id')->first();

    expect($sent->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::AUTO_SENT)
        ->and($sent->reply_message_id)->toBe($answer->id)
        ->and($answer->message_text)->toContain("Your order $reference is packed")
        ->and($answer->message_text)->toContain('This is an automatic reply');

    // In working hours a person is there: it waits for them, earned or not.
    $inHours = $askAt('2026-09-28 11:00', 'And when will it be dispatched?');
    expect($inHours->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::PENDING);

    // Staff flag the automatic answer as wrong from the inbox, saying why: the gate closes again.
    actingAs($this->user);
    $this->post(route('grp.chat.ai.drafts.flag', [$sent->id]))->assertSessionHasErrors('reason');
    expect($sent->refresh()->flagged_wrong_at)->toBeNull();

    $this->postJson(route('grp.chat.ai.sent.flag', ['chat', $answer->id]), ['reason' => 'The order was already dispatched'])
        ->assertOk()->assertJson(['success' => true]);
    expect($sent->refresh()->flagged_wrong_at)->not->toBeNull()
        ->and($sent->flagged_reason)->toBe('The order was already dispatched')
        ->and($sent->flagged_by_user_id)->toBe($this->user->id)
        ->and(Arr::get($answer->refresh()->metadata, 'flagged_wrong_at'))->not->toBeNull();

    $this->postJson(route('grp.chat.ai.sent.flag', ['chat', $answer->id]), ['reason' => 'A second opinion'])->assertStatus(422);
    expect($sent->refresh()->flagged_reason)->toBe('The order was already dispatched')
        ->and(\App\Actions\Chat\ChatSession\GetChatAutoSendGate::run($this->shop, \App\Enums\CRM\Livechat\ChatTopicEnum::ORDER_STATUS)['earned'])->toBeFalse();

    $afterFlag = $askAt('2026-09-28 20:00', 'Any news on my order?');
    expect($afterFlag->status)->toBe(\App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::PENDING);

    \App\Models\Chat\ChatAiDraft::where('chat_session_id', $session->id)->delete();
    outOfHoursTestCleanUp($schedule, [$session]);
});

test('an email out of hours gets the closed-now reply at once and never an AI answer, even on a topic that earned one', function () {
    config([
        'chat.out_of_hours_reply'        => true,
        'chat.ai_drafts'                 => true,
        'chat.ai_auto_send.enabled'      => true,
        'chat.ai_auto_send.min_decided'  => 3,
        'askbot-laravel.openai_api_key'  => 'test-key',
    ]);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class, \App\Actions\Comms\Mailbox\SendChatMessageByGmail::class]);
    $schedule = outOfHoursTestSchedule($this->shop);
    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-09-26 11:00', 'Europe/London'));

    $customer  = createOwnCustomer($this->shop, 'ai-email-once');
    $webUser   = \App\Actions\CRM\WebUser\StoreWebUser::make()->action($customer, WebUser::factory()->definition());
    $reference = 'AEO'.random_int(100000, 999999);

    \Illuminate\Support\Facades\DB::table('orders')->insert([
        'group_id'        => $this->shop->group_id,
        'organisation_id' => $this->shop->organisation_id,
        'shop_id'         => $this->shop->id,
        'customer_id'     => $customer->id,
        'currency_id'     => $this->shop->currency_id,
        'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id,
        'slug'            => 'ord-'.uniqid(),
        'reference'       => $reference,
        'state'           => 'packed',
        'net_amount'      => 100,
        'org_net_amount'  => 100,
        'grp_net_amount'  => 100,
        'status'          => \App\Enums\Ordering\Order\OrderStatusEnum::CREATING,
        'payment_data'    => '{}',
        'data'            => '{}',
        'date'            => '2026-09-24',
        'submitted_at'    => '2026-09-24 09:00:00',
        'created_at'      => '2026-09-24',
        'updated_at'      => '2026-09-24',
    ]);

    fakeDraftLanguageDetection();
    \Illuminate\Support\Facades\Http::fake([
        'api.openai.com/*' => fn ($request) => aiDraftTestModel($request, json_encode([
            'answerable' => true, 'topic' => 'order_status', 'reply' => "Your order $reference is packed and waiting for the courier.",
        ])),
        'oauth2.googleapis.com/*' => \Illuminate\Support\Facades\Http::response(['access_token' => 'at']),
        '*'                       => \Illuminate\Support\Facades\Http::response(['id' => 'sent-1', 'threadId' => 'th-once']),
    ]);

    $emailFrom = function () use ($webUser) {
        $session = noiseTestEmailSession($this->shop->fresh(), 'ai.once.'.Str::lower(Str::random(8)).'@example.com', 'My order', 'Where is my order please?', ['gmail_message_id' => 'in-1']);
        $session->update(['web_user_id' => $webUser->id]);

        return $session;
    };
    $trigger        = fn (ChatSession $session) => $session->messages()->where('sender_type', ChatSenderTypeEnum::GUEST)->latest('id')->first();
    $automaticMails = fn (ChatSession $session) => $session->messages()->where('sender_type', ChatSenderTypeEnum::SYSTEM)->count();

    $answered = $emailFrom();
    foreach (range(1, 3) as $i) {
        \App\Models\Chat\ChatAiDraft::create([
            'group_id' => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id,
            'chat_session_id' => $answered->id, 'topic' => 'order_status', 'facts' => [], 'text' => 'earned '.$i,
            'status' => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::USED,
        ]);
    }

    expect(\App\Actions\Chat\ChatSession\SendOutOfHoursReply::makeJob($answered, $trigger($answered))->delay)->toBeNull()
        ->and(\App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($answered->refresh()))->toBeNull()
        ->and(\App\Actions\Chat\ChatSession\SendOutOfHoursReply::make()->handle($answered, $trigger($answered)))->toBeTrue()
        ->and($automaticMails($answered))->toBe(1);

    \Illuminate\Support\Facades\Cache::forget('chat-out-of-hours-email:'.sha1($answered->metadata['email_from']));
    \App\Models\Chat\ChatAiDraft::where('chat_session_id', $answered->id)->delete();
    outOfHoursTestCleanUp($schedule, [$answered]);
});

test('what agents keep telling different customers is learned and put to staff once enough customers heard it, and only a person turns it on', function () {
    $shop = $this->shop;
    \App\Actions\Helpers\AI\EmbedTexts::mock()->shouldReceive('handle')->andReturnUsing(fn (array $texts) => array_map(fn (string $text) => array_pad(str_contains($text, 'VAT') ? [1.0, 0.0] : [0.0, 1.0], 1024, 0.0), $texts));
    \App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $shop->id)->delete();
    $customers = [];
    $thread    = function (string $question, string $reply, string $key, ?string $customerKey = null) use ($shop, &$customers) {
        $customerKey ??= $key;
        $customers[$customerKey] ??= StoreCustomer::make()->action($shop, Customer::factory()->definition());
        $base = ['group_id' => $shop->group_id, 'organisation_id' => $shop->organisation_id, 'shop_id' => $shop->id, 'customer_id' => $customers[$customerKey]->id, 'gmail_thread_id' => 'th-learn-'.$key, 'subject' => 'Question', 'counterpart_address' => "learn.$key@example.com"];
        \App\Models\Comms\EmailArchiveMessage::create($base + ['gmail_message_id' => "q-$key", 'is_outbound' => false, 'text' => $question, 'sent_at' => now()->subDays(2)]);
        \App\Models\Comms\EmailArchiveMessage::create($base + ['gmail_message_id' => "r-$key", 'is_outbound' => true, 'text' => $reply, 'sent_at' => now()->subDay()]);
    };
    foreach (['a', 'b'] as $key) {
        $thread('Is VAT charged on shipping?', 'Hi, yes VAT applies to both the products and the shipping cost, standard UK rate of 20%.', $key);
    }

    $rule = ['general' => true, 'title' => 'VAT on shipping', 'note' => 'VAT is charged on products and on shipping at the standard UK rate of 20%.', 'temporary' => false];
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->andReturnUsing(function () use (&$rule) {
        return json_encode($rule);
    });
    $contradicts = 0.1;
    $jev = \App\Actions\Helpers\AI\AskJev::mock();
    $jev->shouldReceive('handle')->andReturnUsing(function (array $state, array $questions) {
        $options = array_keys($questions['same']['criteria'] ?? $questions['entry']['criteria'] ?? []);
        $first   = $options[0] ?? 'new';

        $note = (string) ($state['new_note'] ?? '');
        $same = collect($questions['same']['criteria'] ?? [])
            ->filter(fn (string $label, string $key) => $key !== 'new' && collect(['VAT', 'dispatched'])->contains(fn (string $word) => str_contains($label, $word) && str_contains($note, $word)))
            ->keys()
            ->first() ?? 'new';

        return isset($questions['same'])
            ? ['same' => ['choice' => $same, 'probabilities' => [$same => 0.9]]]
            : ['entry' => ['choice' => $first, 'probabilities' => [$first => 0.9]]];
    });
    $jev->shouldReceive('noul')->andReturnUsing(function () use (&$contradicts) {
        return $contradicts;
    });

    $twice = \App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop);
    $vat   = \App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $shop->id)->where('source_type', 'learned')->sole();
    expect($twice)->toMatchArray(['replies' => 2, 'rules' => 2, 'promoted' => 0])
        ->and($vat->only(['status', 'customers_count']))->toBe(['status' => 'candidate', 'customers_count' => 2])
        ->and($vat->embedding)->toHaveCount(1024)
        ->and(\App\Models\Chat\ChatKnowledgeEntry::forShop($shop)->pluck('id'))->not->toContain($vat->id)
        ->and(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop)['replies'])->toBe(0);

    $thread('Is VAT added to delivery?', 'Yes, VAT at 20% is added to the delivery charge too, like on the products.', 'a-again', 'a');
    $thread('A question without a customer', 'VAT at the standard rate of 20% applies to delivery as well as to goods.', 'stranger');
    \App\Models\Comms\EmailArchiveMessage::where('gmail_thread_id', 'th-learn-stranger')->update(['customer_id' => null]);
    expect(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop))->toMatchArray(['replies' => 1, 'promoted' => 0])
        ->and($vat->refresh()->only(['status', 'customers_count']))->toBe(['status' => 'candidate', 'customers_count' => 2]);

    $thread('Do you charge VAT on delivery?', 'Yes, 20% VAT is added to the delivery charge as well.', 'c');
    expect(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop)['promoted'])->toBe(1)
        ->and($vat->refresh()->only(['status', 'customers_count']))->toBe(['status' => 'proposed', 'customers_count' => 3])
        ->and(\App\Models\Chat\ChatKnowledgeEntry::forShop($shop)->pluck('id'))->not->toContain($vat->id);

    $vat->update(['status' => 'active']);
    expect(\App\Models\Chat\ChatKnowledgeEntry::forShop($shop)->pluck('id'))->toContain($vat->id);

    $vat->update(['status' => 'removed']);
    $contradicts = 0.8;
    $rule        = ['general' => true, 'title' => 'Dispatch', 'note' => 'Orders are dispatched the same day.', 'temporary' => false];
    \App\Models\Chat\ChatKnowledgeEntry::create(['group_id' => $shop->group_id, 'organisation_id' => $shop->organisation_id, 'shop_id' => $shop->id, 'kind' => 'note', 'title' => 'Dispatch times', 'body' => 'Orders are dispatched within 2 working days.', 'source_type' => 'manual', 'is_manual' => true]);
    foreach (['d', 'e', 'f'] as $key) {
        $thread('When do you dispatch?', 'We dispatch every order the same day it is placed.', $key);
    }
    \App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop);
    $dispatch = \App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $shop->id)->where('source_type', 'learned')->where('title', 'Dispatch')->first();

    expect($dispatch?->only(['status', 'customers_count', 'conflict']))->toBe(['status' => 'conflict', 'customers_count' => 3, 'conflict' => 'Dispatch times'])
        ->and(\App\Models\Chat\ChatKnowledgeEntry::forShop($shop)->where('source_type', 'learned')->count())->toBe(0);

    $rule = null;
    $thread('Do you ship to Spain?', 'Yes, we deliver to Spain within five working days of dispatch.', 'no-answer');
    expect(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop)['rules'])->toBe(0)
        ->and(\App\Models\Comms\EmailArchiveMessage::where('gmail_message_id', 'r-no-answer')->value('learned_at'))->toBeNull();

    $rule = ['general' => true, 'title' => 'Missing items', 'note' => 'A missing item on a shipped order is credited to the account.', 'temporary' => false];
    $thread('One candle is missing', 'Sorry about that, we have credited the missing candle to your account.', 'years-ago');
    \App\Models\Comms\EmailArchiveMessage::where('gmail_message_id', 'q-years-ago')->update(['sent_at' => now()->subYears(5)->subDay()]);
    \App\Models\Comms\EmailArchiveMessage::where('gmail_message_id', 'r-years-ago')->update(['sent_at' => now()->subYears(5)]);
    expect(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop)['replies'])->toBe(1)
        ->and(\App\Actions\Chat\ChatSession\LearnChatKnowledge::run($shop, 0)['replies'])->toBe(1)
        ->and(\App\Models\Comms\EmailArchiveMessage::where('gmail_message_id', 'r-years-ago')->value('learned_at'))->not->toBeNull();

    \App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $shop->id)->delete();
    \App\Models\Comms\EmailArchiveMessage::where('gmail_thread_id', 'like', 'th-learn-%')->delete();
});

test('order facts follow a replacement sent lately for an older order, and products named in words are found by the shop search', function () {
    $customer = createOwnCustomer($this->shop, 'facts-replacement');
    $order    = fn (string $reference, string $date) => \App\Models\Ordering\Order::find(\Illuminate\Support\Facades\DB::table('orders')->insertGetId([
        'group_id' => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id, 'customer_id' => $customer->id,
        'currency_id' => $this->shop->currency_id, 'tax_category_id' => \App\Models\Helpers\TaxCategory::firstOrFail()->id, 'slug' => 'ord-'.uniqid(), 'reference' => $reference,
        'state' => 'dispatched', 'net_amount' => 100, 'org_net_amount' => 100, 'grp_net_amount' => 100, 'status' => \App\Enums\Ordering\Order\OrderStatusEnum::SETTLED,
        'payment_data' => '{}', 'data' => '{}', 'date' => $date, 'submitted_at' => $date, 'dispatched_at' => $date, 'created_at' => $date, 'updated_at' => $date,
    ]));
    $older = $order('REPL'.random_int(100000, 999999), now()->subDays(20)->toDateTimeString());
    $newer = $order('NEWR'.random_int(100000, 999999), now()->subDays(5)->toDateTimeString());

    expect(\App\Actions\Chat\ChatSession\GetChatOrderFacts::run($customer, 'When will it be sent?')['order']['reference'])->toBe($newer->reference);

    $deliveryNoteId = \Illuminate\Support\Facades\DB::table('delivery_notes')->insertGetId([
        'group_id' => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id, 'customer_id' => $customer->id,
        'warehouse_id' => \App\Models\Inventory\Warehouse::where('organisation_id', $this->shop->organisation_id)->firstOrFail()->id, 'slug' => 'dn-'.uniqid(),
        'reference' => $older->reference, 'type' => 'replacement', 'state' => 'handling', 'date' => now(), 'data' => '{}', 'created_at' => now(), 'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('delivery_note_order')->insert(['delivery_note_id' => $deliveryNoteId, 'order_id' => $older->id, 'created_at' => now(), 'updated_at' => now()]);

    $facts = \App\Actions\Chat\ChatSession\GetChatOrderFacts::run($customer, 'When will it be sent?');
    expect($facts['order']['reference'])->toBe($older->reference)
        ->and($facts['order']['replacements'][0]['status'])->toBe(\App\Actions\Chat\ChatSession\GetChatOrderFacts::STATE_MEANING['handling'])
        ->and($facts['order'])->not->toHaveKey('parcels')
        ->and(collect($facts['other_recent_orders'])->pluck('reference'))->toContain($newer->reference);

    config(['scout.driver' => 'collection']);
    $product = Product::where('shop_id', $this->shop->id)->first() ?? createProduct($this->shop)[1];
    $product->update(['name' => 'Tropical Paradise Soap Loaf - Papaya', 'is_in_website' => true, 'is_for_sale' => true]);
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->once()->andReturn('{"names": ["Soap Loaf"]}');

    $found = \App\Actions\Chat\ChatSession\GetChatProductFactsByName::run($this->shop, 'The soap loafs, what is a good starting order?');
    expect(collect($found)->pluck('code'))->toContain($product->code)
        ->and($found[0]['found_by_name'])->toBeTrue()
        ->and(\App\Actions\Chat\ChatSession\GetChatProductFactsByName::run($this->shop, 'Is TPSoap-03 in stock?'))->toBe([]);

    expect(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::isGoodEnough(['answers' => 1.0, 'invents' => 0.0, 'claims_done' => 0.6, 'staff_like' => 1.0, 'send_as_is' => 1.0]))->toBeFalse()
        ->and(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::whatToFix(['answers' => 1.0, 'invents' => 0.0, 'claims_done' => 0.6, 'staff_like' => 1.0, 'send_as_is' => 1.0])[0])->toContain('already did something');

    \Illuminate\Support\Facades\DB::table('delivery_note_order')->where('delivery_note_id', $deliveryNoteId)->delete();
    \Illuminate\Support\Facades\DB::table('delivery_notes')->where('id', $deliveryNoteId)->delete();
    \Illuminate\Support\Facades\DB::table('orders')->whereIn('id', [$older->id, $newer->id])->delete();
});

test('a general question is answered from the knowledge base entry jev picks, and only when the quote is really in it', function () {
    config(['chat.ai_drafts' => true, 'services.openrouter.api_key' => 'or-key', 'chat.suggestion_shop_types' => [$this->shop->type->value]]);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class]);

    $sections = \App\Actions\Chat\ChatSession\HydrateChatKnowledge::sections(['blocks' => ['<h2>Returns</h2><p>You may return goods within 30 days of delivery, unused and in their original packaging.</p><h3>Refunds</h3><p>Refunds are made to your account balance once the goods are back with us.</p>']], 'Returns page');
    expect(array_column($sections, 'title'))->toBe(['Returns page · Returns', 'Returns page · Refunds'])
        ->and($sections[0]['body'])->toBe('You may return goods within 30 days of delivery, unused and in their original packaging.');

    $note = \App\Models\Chat\ChatKnowledgeEntry::create([
        'group_id' => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id,
        'kind' => 'note', 'title' => 'Shipping to Germany', 'source_type' => 'manual', 'is_manual' => true,
        'body' => 'We cannot ship from the UK to Germany because we do not have a LUCID registration.',
    ]);
    expect(\App\Models\Chat\ChatKnowledgeEntry::forShop($this->shop)->pluck('id'))->toContain($note->id);

    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturnUsing(fn (array $state, array $questions) => match (true) {
        isset($questions['wants_something']) => [
            'wants_something' => ['type' => 'noul', 'noul' => 0.97], 'one_question' => ['type' => 'noul', 'noul' => 0.9],
            'problem' => ['type' => 'noul', 'noul' => 0.02], 'answers_us' => ['type' => 'noul', 'noul' => 0.02], 'still_waiting' => ['type' => 'noul', 'noul' => 0.02],
            'subject' => ['type' => 'choice', 'choice' => 'shop', 'probabilities' => ['shop' => 0.95]],
        ],
        isset($questions['ask'])   => ['ask' => ['type' => 'choice', 'choice' => 'ship_to_country', 'probabilities' => ['ship_to_country' => 0.96]]],
        isset($questions['entry']) => ['entry' => ['type' => 'choice', 'choice' => 'e'.$note->id, 'probabilities' => ['e'.$note->id => 0.9, 'none' => 0.1]]],
        isset($questions['covered']) => ['needs_reply' => ['type' => 'noul', 'noul' => 0.9], 'covered' => ['type' => 'score', 'score' => 3]],
        default                    => null,
    });
    $quote = 'We cannot ship from the UK to Germany because we do not have a LUCID registration.';
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->andReturnUsing(function (string $prompt) use (&$quote) {
        return str_contains($prompt, '{"objection"')
            ? '{"objection": "", "send": true}'
            : json_encode(['answerable' => true, 'quote' => $quote, 'reply' => 'Hello! We cannot ship from the UK to Germany, as we have no LUCID registration.']);
    });
    \App\Actions\Helpers\Translations\DetectLanguageWithJev::shouldRun()->andReturn(Language::where('code', 'en')->first());

    $ask = function (string $text) {
        $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::WAITING, 'channel' => ChatChannelEnum::WEBSITE, 'shop_id' => $this->shop->id]);
        ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => $text]);

        return \App\Actions\Chat\ChatSession\DraftChatReply::make()->handle($session);
    };

    $draft = $ask('Do you ship from the UK to Germany?');
    expect($draft->topic)->toBe(\App\Enums\CRM\Livechat\ChatTopicEnum::OTHER)
        ->and($draft->text)->toContain('LUCID')
        ->and($draft->facts['knowledge'])->toBe($note->id)
        ->and($draft->facts['ask'])->toBe('ship_to_country');

    $quote = 'We ship to Germany every single working day of the week.';
    $notFromThePage = $ask('Can you deliver to Germany please?');
    expect(data_get($notFromThePage?->facts, 'knowledge'))->toBeNull()
        ->and(data_get($notFromThePage?->facts, 'mode'))->toBe(\App\Actions\Chat\ChatSession\DraftChatReply::SUGGESTION);

    $quote = 'have a LUCID registration.';
    expect(data_get($ask('Do you have a LUCID registration for Germany?')?->facts, 'knowledge'))->toBeNull();

    $saved = \App\Actions\Chat\UpdateShopChatKnowledgeNote::make()->handle($this->shop, null, ['title' => 'Testers', 'body' => 'Diffuser testers are not available until the website variants are fixed.'], $this->user);
    \App\Actions\Chat\UpdateShopChatKnowledgeNote::make()->handle($this->shop, $saved, ['title' => 'Diffuser testers', 'body' => $saved->body]);
    \App\Actions\Chat\ChatSession\HydrateChatKnowledge::run($this->shop);

    expect($saved->refresh()->only(['title', 'is_manual', 'kind', 'created_by_user_id']))->toBe(['title' => 'Diffuser testers', 'is_manual' => true, 'kind' => 'note', 'created_by_user_id' => $this->user->id])
        ->and(\App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $this->shop->id)->where('is_manual', true)->count())->toBe(2)
        ->and(\App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $this->shop->id)->where('is_manual', false)->whereNotNull('hydrated_at')->count())
        ->toBe(\App\Models\Chat\ChatKnowledgeEntry::where('shop_id', $this->shop->id)->where('is_manual', false)->count());

    $note->delete();
    $saved->delete();
});

test('when only a programmer can fix it staff get one click: the customer joins the open bug it matches, or a CUS ticket is raised, once', function () {
    config()->set('services.openrouter.api_key', 'or-key');
    $user  = User::factory()->create(['group_id' => $this->organisation->group_id]);
    $agent = ChatAgent::create(['user_id' => $user->id, 'max_concurrent_chats' => 5, 'language_id' => 68, 'is_online' => true, 'is_available' => true, 'current_chat_count' => 0]);

    $newSession = function (string $text) {
        $session = ChatSession::create([
            'ulid'             => (string) \Illuminate\Support\Str::ulid(),
            'shop_id'          => $this->shop->id,
            'language_id'      => 68,
            'status'           => ChatSessionStatusEnum::ACTIVE->value,
            'priority'         => ChatPriorityEnum::NORMAL->value,
            'guest_identifier' => 'guest-'.\Illuminate\Support\Str::random(8),
        ]);
        $session->messages()->create(['message_text' => $text, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST]);

        return $session;
    };

    $known = \App\Actions\Chat\ChatSession\StoreTicketFromChatSession::make()->handle($newSession('Stock stuck at zero'), $agent, ['summary' => 'Shopify stock not updating', 'kind' => 'bug']);

    $knownOrNew = $known->reference;
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturnUsing(function (array $state, array $questions) use (&$knownOrNew) {
        return match (true) {
            isset($questions['needs_engineer']) => ['needs_engineer' => ['type' => 'noul', 'noul' => 0.72], 'wants_something' => ['type' => 'noul', 'noul' => 0.9]],
            isset($questions['platform'])       => [
                'platform'     => ['type' => 'choice', 'choice' => 'shopify', 'probabilities' => ['shopify' => 0.9]],
                'symptom'      => ['type' => 'choice', 'choice' => 'stock_wrong', 'probabilities' => ['stock_wrong' => 0.8]],
                'known_ticket' => ['type' => 'choice', 'choice' => $knownOrNew, 'probabilities' => [$knownOrNew => 0.85]],
            ],
            default => null,
        };
    });

    $sameBug = $newSession('My Shopify stock shows 0 for everything since yesterday');
    $turn    = \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($sameBug);

    expect($turn['engineer'])->toMatchArray(['probability' => 0.72, 'platform_label' => 'Shopify', 'ticket' => ['reference' => $known->reference, 'subject' => 'Shopify stock not updating']]);

    $added = \App\Actions\Chat\ChatSession\RaiseChatEngineerTicket::make()->handle($sameBug->refresh(), $agent);
    expect(\App\Models\Chat\ChatTurnReading::where('chat_session_id', $sameBug->id)->latest('id')->first()->only(['engineer', 'used', 'engineer_ticket']))
        ->toBe(['engineer' => true, 'used' => 'engineer', 'engineer_ticket' => $known->reference]);
    expect($added)->toMatchArray(['reference' => $known->reference, 'added' => true])
        ->and($known->comments()->latest('id')->value('body'))->toContain('My Shopify stock shows 0')
        ->and(\App\Actions\Chat\ChatSession\RaiseChatEngineerTicket::make()->handle($sameBug->refresh(), $agent))->toBeNull()
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($sameBug)['engineer']['raised'])->toBe($known->reference);

    $knownOrNew = 'new';
    $newBug     = $newSession('Our Shopify stock is wrong again');
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($newBug);
    $raised = \App\Actions\Chat\ChatSession\RaiseChatEngineerTicket::make()->handle($newBug->refresh(), $agent);
    $ticket = \App\Models\Helpers\Ticket::where('reference', $raised['reference'])->first();

    expect($raised['added'])->toBeFalse()
        ->and($ticket->type)->toBe(\App\Enums\Helpers\Ticket\TicketTypeEnum::CUSTOMER)
        ->and($ticket->kind)->toBe(\App\Enums\Helpers\Ticket\TicketKindEnum::BUG)
        ->and($ticket->subject)->toBe('Shopify · Stock shown wrong or not updating')
        ->and($ticket->description)->toContain('Our Shopify stock is wrong again');

    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($newSession('The Shopify stock is broken'))['engineer'])->not->toBeNull();

    $act = fn (string $act, float $p, float $wants = 0.1) => \App\Actions\Chat\ChatSession\ClassifyChatTurn::nextStep(['wants_something' => ['noul' => $wants], 'act' => ['choice' => $act, 'probabilities' => [$act => $p]]]);
    expect($act('closing', 0.82))->toBe(['kind' => 'close', 'probability' => 0.82])
        ->and($act('informing', 0.9)['kind'])->toBe('wait')
        ->and($act('pending', 0.75)['kind'])->toBe('owed')
        ->and($act('closing', 0.5))->toBeNull()
        ->and($act('closing', 0.95, 0.8))->toBeNull();

    $reply = $newBug->messages()->create(['message_text' => 'Passed to our developers, we will be back to you', 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::AGENT]);
    \App\Actions\Chat\ChatSession\SettleChatAiDraft::run($newBug, $reply);
    expect(\App\Models\Chat\ChatTurnReading::where('chat_session_id', $newBug->id)->latest('id')->first()->only(['reply_message_id', 'reply', 'customer_wrote']))
        ->toBe(['reply_message_id' => $reply->id, 'reply' => 'Passed to our developers, we will be back to you', 'customer_wrote' => 'Our Shopify stock is wrong again']);

    $guides = ['a' => ['title' => 'A', 'summary' => '', 'url' => 'https://shop.test/docs/a'], 'b' => ['title' => 'B', 'summary' => '', 'url' => 'https://shop.test/docs/b']];
    $picked = fn (array $probabilities) => array_column(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestedGuides($guides, ['guide' => ['probabilities' => $probabilities]], 'en'), 'title');
    expect($picked(['a' => 0.72, 'b' => 0.26]))->toBe(['A', 'B'])
        ->and($picked(['a' => 0.9, 'b' => 0.1]))->toBe(['A'])
        ->and($picked(['a' => 0.6, 'b' => 0.4]))->toBe([]);
});

test('a ticket marked as blocking holds the chat open until it is settled', function () {
    $session = ChatSession::create([
        'ulid'             => (string) \Illuminate\Support\Str::ulid(),
        'shop_id'          => $this->shop->id,
        'language_id'      => 68,
        'status'           => ChatSessionStatusEnum::ACTIVE->value,
        'priority'         => ChatPriorityEnum::NORMAL->value,
        'guest_identifier' => 'guest-'.\Illuminate\Support\Str::random(8),
    ]);

    $user  = User::factory()->create(['group_id' => $this->organisation->group_id]);
    $agent = ChatAgent::create([
        'user_id'              => $user->id,
        'max_concurrent_chats' => 5,
        'language_id'          => 68,
        'is_online'            => true,
        'is_available'         => true,
        'current_chat_count'   => 0,
    ]);

    $ticket = \App\Actions\Chat\ChatSession\StoreTicketFromChatSession::make()->handle($session, $agent, [
        'summary'       => 'Refund never arrived',
        'blocks_source' => true,
    ]);

    expect($ticket->blocks_source)->toBeTrue();

    expect(fn () => CloseChatSession::make()->handle($session, $agent->id))
        ->toThrow(\Illuminate\Validation\ValidationException::class, $ticket->reference);

    // the customer ending the chat from their side is not held up by our backlog
    $otherSession = ChatSession::create([
        'ulid'             => (string) \Illuminate\Support\Str::ulid(),
        'shop_id'          => $this->shop->id,
        'language_id'      => 68,
        'status'           => ChatSessionStatusEnum::ACTIVE->value,
        'priority'         => ChatPriorityEnum::NORMAL->value,
        'guest_identifier' => 'guest-'.\Illuminate\Support\Str::random(8),
    ]);
    \App\Actions\Chat\ChatSession\StoreTicketFromChatSession::make()->handle($otherSession, $agent, [
        'summary'       => 'Also blocking',
        'blocks_source' => true,
    ]);

    expect(CloseChatSession::make()->handle($otherSession, null, \App\Enums\CRM\Livechat\ChatActorTypeEnum::GUEST)->status)
        ->toBe(ChatSessionStatusEnum::CLOSED);

    $ticket->update(['status' => \App\Enums\Helpers\Ticket\TicketStatusEnum::RESOLVED->value]);

    expect(CloseChatSession::make()->handle($session->fresh(), $agent->id)->status)
        ->toBe(ChatSessionStatusEnum::CLOSED);
});

test('a task asked of a colleague from a chat labels it in the inbox and holds it open until done', function () {
    $session = ChatSession::create([
        'ulid'             => (string) \Illuminate\Support\Str::ulid(),
        'shop_id'          => $this->shop->id,
        'language_id'      => 68,
        'status'           => ChatSessionStatusEnum::ACTIVE->value,
        'priority'         => ChatPriorityEnum::NORMAL->value,
        'guest_identifier' => 'guest-'.\Illuminate\Support\Str::random(8),
    ]);
    ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT->value,
        'sender_type'     => ChatSenderTypeEnum::GUEST->value,
        'message_text'    => 'Please can I request the 51st amendment forms for rose geranium',
        'is_read'         => true,
    ]);

    $task = \App\Actions\Chat\ChatSession\StoreStaffTaskFromChatSession::make()->handle($session, $this->user, [
        'subject'     => 'Awaiting amendment forms from Aromatics',
        'assignee_id' => $this->user->id,
    ]);

    expect($task->model_type)->toBe('ChatSession')
        ->and($task->model_id)->toBe($session->id)
        ->and($task->description)->toContain($session->ulid);

    $listed = collect(GetChatSessions::make()->handle(['ulid' => $session->ulid])->items())->firstWhere('id', $session->id);
    expect(\App\Http\Resources\CRM\Livechat\ChatSessionListResource::make($listed)->resolve()['open_tasks'])
        ->toHaveCount(1)
        ->sequence(fn ($openTask) => $openTask->reference->toBe($task->reference)->subject->toBe('Awaiting amendment forms from Aromatics'));

    $agent = ChatAgent::create([
        'user_id'              => User::factory()->create(['group_id' => $this->organisation->group_id])->id,
        'max_concurrent_chats' => 5,
        'language_id'          => 68,
        'is_online'            => true,
        'is_available'         => true,
        'current_chat_count'   => 0,
    ]);

    expect(fn () => CloseChatSession::make()->handle($session, $agent->id))
        ->toThrow(\Illuminate\Validation\ValidationException::class, $task->reference);

    $task->update(['status' => \App\Enums\Tasks\StaffTaskStatusEnum::DONE]);

    expect(CloseChatSession::make()->handle($session->fresh(), $agent->id)->status)->toBe(ChatSessionStatusEnum::CLOSED);
});

test('a ticket raised from a chat does not block it unless it was marked as blocking', function () {
    $session = ChatSession::create([
        'ulid'             => (string) \Illuminate\Support\Str::ulid(),
        'shop_id'          => $this->shop->id,
        'language_id'      => 68,
        'status'           => ChatSessionStatusEnum::ACTIVE->value,
        'priority'         => ChatPriorityEnum::NORMAL->value,
        'guest_identifier' => 'guest-'.\Illuminate\Support\Str::random(8),
    ]);

    $user  = User::factory()->create(['group_id' => $this->organisation->group_id]);
    $agent = ChatAgent::create([
        'user_id'              => $user->id,
        'max_concurrent_chats' => 5,
        'language_id'          => 68,
        'is_online'            => true,
        'is_available'         => true,
        'current_chat_count'   => 0,
    ]);

    $ticket = \App\Actions\Chat\ChatSession\StoreTicketFromChatSession::make()->handle($session, $agent, [
        'summary' => 'Nice to have, not urgent',
    ]);

    expect($ticket->blocks_source)->toBeFalse()
        ->and(CloseChatSession::make()->handle($session, $agent->id)->status)->toBe(ChatSessionStatusEnum::CLOSED);
});

test('the jobs a customer message starts share one reading of it and never write back each other metadata stale', function () {
    config()->set('services.openrouter.api_key', 'or-key');
    $jevCalls = 0;
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturnUsing(function () use (&$jevCalls) {
        $jevCalls++;

        return ['wants_something' => ['type' => 'noul', 'noul' => 0.1], 'act' => ['type' => 'choice', 'choice' => 'informing', 'probabilities' => ['informing' => 0.5]]];
    });

    $session = ChatSession::create([
        'ulid'     => (string) Str::ulid(),
        'status'   => ChatSessionStatusEnum::WAITING,
        'channel'  => ChatChannelEnum::WEBSITE,
        'shop_id'  => $this->shop->id,
        'metadata' => ['waiting_for_customer' => ['message_id' => 1]],
    ]);
    $message = $session->messages()->create(['message_text' => 'I will send the photos tomorrow', 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST]);

    $urgentJob = ChatSession::find($session->id);
    $draftJob  = ChatSession::find($session->id);
    \App\Actions\Chat\ChatSession\SetChatSessionMetadata::run($urgentJob, [\App\Actions\Chat\ChatSession\FlagUrgentChatRequest::KEY => 'cancel_order', 'waiting_for_customer' => null]);
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::run($draftJob, 'I will send the photos tomorrow', '(nothing yet)', $message->id);
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::run($draftJob, 'I will send the photos tomorrow', '(nothing yet)', $message->id);

    $metadata = $session->refresh()->metadata;
    $reading  = \App\Models\Chat\ChatTurnReading::where('chat_session_id', $session->id)->sole();
    expect($metadata[\App\Actions\Chat\ChatSession\FlagUrgentChatRequest::KEY])->toBe('cancel_order')
        ->and($metadata)->not->toHaveKey('waiting_for_customer')
        ->and($metadata[\App\Actions\Chat\ChatSession\ClassifyChatTurn::KEY]['reading_id'])->toBe($reading->id)
        ->and($draftJob->metadata[\App\Actions\Chat\ChatSession\ClassifyChatTurn::KEY]['reading_id'])->toBe($reading->id);

    $jevCalls = 0;
    \Illuminate\Support\Facades\Cache::flush();
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($session);
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession(ChatSession::find($session->id));
    expect($jevCalls)->toBe(1)
        ->and(\App\Models\Chat\ChatTurnReading::where('chat_session_id', $session->id)->count())->toBe(1);

    \App\Actions\Chat\ChatSession\ClassifyChatTurn::markUsed($session, $reading->id + 1000, 'wait');
    \App\Actions\Chat\ChatSession\ClassifyChatTurn::markUsed($session, $reading->id, 'wait');
    expect($reading->refresh()->used)->toBe('wait');
});

test('a thanks gets the end chat card with no goodbye written until staff ask, an out of office is not read, and an answer sent while jev reads hides the card', function () {
    config()->set('services.openrouter.api_key', 'or-key');
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->never();
    $jevCalls = 0;
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturnUsing(function () use (&$jevCalls) {
        $jevCalls++;

        return ['wants_something' => ['type' => 'noul', 'noul' => 0.05], 'act' => ['type' => 'choice', 'choice' => 'closing', 'probabilities' => ['closing' => 0.8]]];
    });

    $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::WAITING, 'channel' => ChatChannelEnum::EMAIL, 'shop_id' => $this->shop->id]);
    $session->messages()->create(['message_text' => 'Thank you so much, all sorted', 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::USER, 'created_at' => now()->subMinutes(2)]);

    $turn = \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($session);
    expect($turn['next_step'])->toBe(['kind' => 'close', 'probability' => 0.8])
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh())['next_step']['kind'])->toBe('close');

    $session->update(['last_agent_message_at' => now()->subMinute()]);
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh()))->toBeNull();

    $jevCalls = 0;
    $session->messages()->create(['message_text' => 'I am out of the office until Monday', 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::USER, 'metadata' => ['auto_reply' => true]]);
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($session->refresh()))->toBeNull()
        ->and($jevCalls)->toBe(0);
});

test('a close suggestion does not outlive the close: once the session is closed, the AI draft box has nothing to suggest', function () {
    config()->set('services.openrouter.api_key', 'or-key');
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturn([
        'wants_something' => ['type' => 'noul', 'noul' => 0.05],
        'act'              => ['type' => 'choice', 'choice' => 'closing', 'probabilities' => ['closing' => 0.8]],
    ]);

    $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::WAITING, 'channel' => ChatChannelEnum::EMAIL, 'shop_id' => $this->shop->id]);
    $session->messages()->create(['message_text' => 'Thank you so much, all sorted', 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::USER, 'created_at' => now()->subMinutes(2)]);

    \App\Actions\Chat\ChatSession\ClassifyChatTurn::forSession($session);

    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh())['next_step']['kind'])->toBe('close');

    // No new agent message in between — only the close itself — which is exactly the state a
    // revisited, already-closed conversation is left in. The stale "close" suggestion must not
    // survive it, or the quick-action button resurfaces and can close the session a second time.
    $session->update(['status' => ChatSessionStatusEnum::CLOSED->value, 'closed_at' => now()]);

    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh()))->toBeNull();
});

test('jev works out what the customer wants in rounds, and only a clear single question can be drafted', function () {
    config()->set('services.openrouter.api_key', 'or-key');

    $noul   = fn (float $p) => ['type' => 'noul', 'noul' => $p];
    $choice = fn (string $picked, float $p) => ['type' => 'choice', 'choice' => $picked, 'probabilities' => [$picked => $p]];
    $clear  = ['wants_something' => $noul(0.97), 'answers_us' => $noul(0.05), 'about_existing_order' => $noul(0.1), 'problem' => $noul(0.05), 'one_question' => $noul(0.9), 'still_waiting' => $noul(0.02)];

    \Illuminate\Support\Facades\Http::fake([
        'openrouter.ai/api/alpha/decisions' => \Illuminate\Support\Facades\Http::sequence()
            ->push(['answers' => $clear + ['subject' => $choice('stock', 0.95)]])
            ->push(['answers' => ['stock_ask' => $choice('when_back', 0.9), 'names_product_code' => $noul(0.96)]])
            ->push(['answers' => $clear + ['subject' => $choice('stock', 0.95)]])
            ->push(['answers' => ['stock_ask' => $choice('bulk', 0.8), 'names_product_code' => $noul(0.96)]])
            ->push(['answers' => array_merge($clear, ['problem' => $noul(0.9), 'about_existing_order' => $noul(0.9), 'subject' => $choice('order', 0.9)])])
            ->push(['answers' => ['problem_kind' => $choice('damaged', 0.9), 'names_order' => $noul(0.2)]])
            ->push([], 500),
    ]);

    $session = ChatSession::create([
        'ulid'    => (string) Str::ulid(),
        'status'  => ChatSessionStatusEnum::WAITING,
        'channel' => ChatChannelEnum::WEBSITE,
        'shop_id' => $this->shop->id,
    ]);
    $classify = fn (string $text) => \App\Actions\Chat\ChatSession\ClassifyChatTurn::run($session, $text, '(nothing yet)');

    $readingsBefore = \App\Models\Chat\ChatTurnReading::where('chat_session_id', $session->id)->count();
    $whenBack       = $classify('When is NSBag-09 back in stock?');
    expect(\App\Models\Chat\ChatTurnReading::where('chat_session_id', $session->id)->count())->toBe($readingsBefore + 1)
        ->and(\App\Models\Chat\ChatTurnReading::where('chat_session_id', $session->id)->latest('id')->first()->branch)->toBe('stock');
    expect($whenBack['branch'])->toBe('stock')
        ->and($whenBack['topic'])->toBe(\App\Enums\CRM\Livechat\ChatTopicEnum::STOCK_AVAILABILITY)
        ->and($session->refresh()->metadata['ai_turn']['answers']['stock_ask'])->toBe(['choice' => 'when_back', 'probability' => 0.9]);

    expect($classify('Can you get me 400 red FPGB-12?')['topic'])->toBeNull();

    $damaged = $classify('Two candles arrived broken');
    expect($damaged['branch'])->toBe('problem')
        ->and($damaged['topic'])->toBeNull()
        ->and($session->refresh()->metadata['ai_turn']['answers']['problem_kind']['choice'])->toBe('damaged');

    expect($classify('Anything'))->toBeNull();

    $guides = ['connecting-woocommerce' => ['title' => 'Connecting WooCommerce', 'summary' => 'Install the plugin and connect.', 'url' => 'https://shop.test/docs/connecting-woocommerce']];
    $asked  = \App\Actions\Chat\ChatSession\ClassifyChatTurn::secondRound('integration', $guides);
    expect($asked['guide']['criteria'])->toBe(['connecting-woocommerce' => 'Connecting WooCommerce: Install the plugin and connect.', 'none' => 'None of these guides answers it'])
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::secondRound('integration'))->toBe([]);

    $integration = $clear + ['subject' => $choice('integration', 0.95)];
    $howTo       = fn (float $howTo, string $guide, float $p) => \App\Actions\Chat\ChatSession\ClassifyChatTurn::topic('integration', $integration + ['how_to' => $noul($howTo), 'guide' => $choice($guide, $p)]);
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::branch($integration))->toBe('integration')
        ->and($howTo(0.95, 'connecting-woocommerce', 0.9))->toBe(\App\Enums\CRM\Livechat\ChatTopicEnum::DROPSHIPPING_INTEGRATION)
        ->and($howTo(0.05, 'connecting-woocommerce', 0.98))->toBeNull()
        ->and($howTo(0.95, 'none', 0.9))->toBeNull()
        ->and($howTo(0.95, 'connecting-woocommerce', 0.5))->toBeNull();

    expect($damaged['claim'])->toBeTrue()
        ->and($whenBack['claim'])->toBeFalse()
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::urgent(['request' => ['probabilities' => ['cancel_all' => 0.45, 'change_address' => 0.1]]]))->toBe('cancel_order')
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::urgent(['request' => ['probabilities' => ['remove_items' => 0.25, 'hold' => 0.2, 'none' => 0.55]]]))->toBe('cancel_order')
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::urgent(['request' => ['probabilities' => ['cancel_all' => 0.02, 'change_address' => 0.92]]]))->toBe('change_address')
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::urgent(['request' => ['probabilities' => ['cancel_all' => 0.04, 'accept_ours' => 0.94]]]))->toBeNull()
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::urgent(['request' => ['probabilities' => ['cancel_all' => 0.2, 'none' => 0.8]]]))->toBeNull();

    $onlyReports = ['wants_something' => $noul(0.48), 'problem' => $noul(0.98), 'about_existing_order' => $noul(0.96), 'act' => $choice('asking', 0.8)];
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::branch($onlyReports))->toBe('problem')
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::branch(['act' => $choice('closing', 0.9)] + $onlyReports))->toBeNull();

    $hint = ['title' => 'Connecting WooCommerce', 'summary' => 'Install the plugin.', 'url' => 'https://shop.test/docs/connecting-woocommerce', 'probability' => 0.93];
    $session->update(['metadata' => ['ai_turn' => ['at' => now()->toISOString(), 'guides' => [$hint], 'engineer' => null]], 'last_agent_message_at' => now()->subMinute()]);
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh()))->toBe(['reading_id' => null, 'guides' => [$hint], 'engineer' => null, 'facts' => [], 'next_step' => null])
        ->and(\App\Actions\Chat\ChatSession\ClassifyChatTurn::guideMessage($hint, 'en'))->toContain("helps:\nConnecting WooCommerce\nhttps://shop.test/docs/connecting-woocommerce\n");
    $session->update(['last_agent_message_at' => now()->addMinute()]);
    expect(\App\Actions\Chat\ChatSession\ClassifyChatTurn::suggestions($session->refresh()))->toBeNull();

    $firstRound = \Illuminate\Support\Facades\Http::recorded()->first()[0]->data();
    expect(array_keys($firstRound['questions']))->toContain('wants_something', 'subject')
        ->and($firstRound['state']['customer_wrote'])->toBe('When is NSBag-09 back in stock?');

    $session->forceDelete();
});

test('a claim gets a suggested reply with gaps for the agent, never sent on its own, and a number aiku does not hold drops it', function () {
    config(['chat.ai_drafts' => true, 'services.openrouter.api_key' => 'or-key', 'chat.suggestion_shop_types' => [$this->shop->type->value]]);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class]);
    fakeDraftLanguageDetection();

    $noul = fn (float $p) => ['type' => 'noul', 'noul' => $p];
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturn([
        'wants_something' => $noul(0.9), 'problem' => $noul(0.97), 'about_existing_order' => $noul(0.95), 'one_question' => $noul(0.2),
        'act'             => ['type' => 'choice', 'choice' => 'asking', 'probabilities' => ['asking' => 0.9]],
        'problem_kind'    => ['type' => 'choice', 'choice' => 'damaged', 'probabilities' => ['damaged' => 0.95]],
        'covered'         => ['type' => 'score', 'score' => 3],
    ]);

    $reply = 'Hello, I am sorry two mugs arrived broken. Could you send a photo of each mug and of the box? [[agent: replacement or credit?]]';
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->andReturnUsing(function () use (&$reply) {
        return json_encode(['topic' => 'missing_or_damaged', 'reply' => $reply]);
    });

    $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::ACTIVE, 'channel' => ChatChannelEnum::WEBSITE, 'shop_id' => $this->shop->id, 'last_visitor_message_at' => now()]);
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'Two of the mugs arrived broken, what can you do?']);

    config(['chat.suggestion_shop_types' => []]);
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::run($session))->toBeNull();
    config(['chat.suggestion_shop_types' => [$this->shop->type->value]]);

    $draft = \App\Actions\Chat\ChatSession\DraftChatReply::run($session);

    expect($draft?->text)->toBe($reply)
        ->and($draft->topic)->toBe(\App\Enums\CRM\Livechat\ChatTopicEnum::MISSING_OR_DAMAGED)
        ->and(data_get($draft->facts, 'mode'))->toBe(\App\Actions\Chat\ChatSession\DraftChatReply::SUGGESTION)
        ->and(data_get($draft->facts, 'model'))->toBe(\App\Actions\Chat\ChatSession\DraftChatReply::suggestionModel($session))
        ->and(\App\Actions\Chat\ChatSession\GetChatAutoSendGate::run($this->shop, \App\Enums\CRM\Livechat\ChatTopicEnum::MISSING_OR_DAMAGED)['decided'])->toBe(0);

    config(['chat.ai_auto_send.enabled' => true]);
    expect(\App\Actions\Chat\ChatSession\SendChatAiAnswer::run($draft))->toBeFalse()
        ->and(preg_match(\App\Actions\Chat\ChatSession\DraftChatReply::GAP, $reply))->toBe(1)
        ->and(\App\Actions\Chat\ChatSession\DraftChatReply::usesOnlyKnownNumbers('Your order GB589594 left on 29 September.', 'order GB589594 dispatched 2026-09-29, 29 September'))->toBeTrue()
        ->and(\App\Actions\Chat\ChatSession\DraftChatReply::usesOnlyKnownNumbers('Your order GB123456 left today.', 'order GB589594'))->toBeFalse();

    $writers = collect(range(1, 40))->map(fn (int $id) => \App\Actions\Chat\ChatSession\DraftChatReply::suggestionModel((new ChatSession())->forceFill(['id' => $id])))->unique()->sort()->values()->all();
    expect($writers)->toBe(collect(config('chat.suggestion_models'))->sort()->values()->all());
    config(['chat.suggestion_model' => 'openai/gpt-5.6-luna']);
    expect(\App\Actions\Chat\ChatSession\DraftChatReply::suggestionModel($session))->toBe('openai/gpt-5.6-luna');

    $draft->update(['status' => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::DISCARDED]);
    config(['chat.suggestion_shadow_shops' => [$this->shop->slug]]);
    \Illuminate\Support\Facades\Event::fake([\App\Events\BroadcastChatAiDraft::class]);
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'And the teapot lid is cracked.']);
    $shadow = \App\Actions\Chat\ChatSession\DraftChatReply::run($session->refresh());

    expect(data_get($shadow?->facts, 'shadow'))->toBeTrue()
        ->and(\App\Actions\Chat\ChatSession\DraftChatReply::isShadow('another-shop', $shadow->facts))->toBeFalse();
    \Illuminate\Support\Facades\Event::assertNotDispatched(\App\Events\BroadcastChatAiDraft::class);
    config(['chat.suggestion_shadow_shops' => []]);

    $shadow->update(['status' => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::DISCARDED]);
    $reply = 'Hello, your order GB123456 will be replaced. [[agent: confirm]]';
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'Also one candle holder is chipped.']);

    expect(\App\Actions\Chat\ChatSession\DraftChatReply::run($session->refresh()))->toBeNull();

    $session->forceDelete();
});

test('suggestions staff answered after are scored against the real reply once, per shop with shadow apart', function () {
    $session  = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::ACTIVE, 'channel' => ChatChannelEnum::WEBSITE, 'shop_id' => $this->shop->id]);
    $customer = ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'Do you ship to Spain?']);
    $staff    = ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::AGENT, 'message_text' => 'Yes, every day.']);
    $day      = now('UTC')->subYears(3)->startOfDay();
    $draft    = \App\Models\Chat\ChatAiDraft::create([
        'group_id'         => $this->shop->group_id, 'organisation_id' => $this->shop->organisation_id, 'shop_id' => $this->shop->id,
        'chat_session_id'  => $session->id, 'trigger_message_id' => $customer->id, 'topic' => \App\Enums\CRM\Livechat\ChatTopicEnum::OTHER,
        'facts'            => ['mode' => \App\Actions\Chat\ChatSession\DraftChatReply::SUGGESTION, 'shadow' => true], 'text' => 'Yes, we ship to Spain daily.',
        'status'           => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::SUPERSEDED, 'reply_message_id' => $staff->id, 'decided_at' => $day->copy()->addHours(10),
    ]);

    \App\Actions\Chat\ChatSession\JudgeChatSuggestion::mock()->shouldReceive('nearMatch')->once()
        ->with('Do you ship to Spain?', 'Yes, every day.', 'Yes, we ship to Spain daily.')
        ->andReturn(['score' => 3, 'label' => 'nearly the same']);

    expect(\App\Actions\Chat\ChatSession\ScoreChatSuggestions::run($day))->toBe([$this->shop->slug.' (shadow)' => ['scored' => 1, 'close' => 1]])
        ->and(data_get($draft->refresh()->facts, 'near_match.score'))->toBe(3)
        ->and(\App\Actions\Chat\ChatSession\ScoreChatSuggestions::run($day))->toBe([]);

    $session->forceDelete();
});

test('agent replies become examples embedded by what the customer meant, found by meaning, never from after the cut-off', function () {
    config(['services.openrouter.api_key' => 'or-key']);
    $vectorFor = fn (string $text) => array_pad(str_contains(mb_strtolower($text), 'broken') || str_contains(mb_strtolower($text), 'smashed') ? [1.0, 0.0] : [0.0, 1.0], 1024, 0.0);
    \App\Actions\Helpers\AI\EmbedTexts::mock()->shouldReceive('handle')->andReturnUsing(fn (array $texts) => array_map($vectorFor, $texts));

    $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::ACTIVE, 'channel' => ChatChannelEnum::WEBSITE, 'shop_id' => $this->shop->id]);
    $say     = fn (string $text, ChatSenderTypeEnum $sender) => ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => $sender, 'message_text' => $text]);
    $say('Two mugs arrived broken', ChatSenderTypeEnum::GUEST);
    $say("Sorry! Send us a photo please.\n\nOn Tue, 1 Sep 2026 at 10:00, Ann wrote:\n> Two mugs arrived broken", ChatSenderTypeEnum::AGENT);
    $say('Do you ship to Spain?', ChatSenderTypeEnum::GUEST);
    $say('Yes, every day.', ChatSenderTypeEnum::AGENT);

    $result = \App\Actions\Chat\ChatSession\HydrateChatReplyExamples::run();
    $mine   = \App\Models\Chat\ChatReplyExample::where('shop_id', $this->shop->id)->where('source', 'chat')->whereIn('customer_wrote', ['Two mugs arrived broken', 'Do you ship to Spain?']);

    expect($result['collected'])->toBeGreaterThanOrEqual(2)
        ->and((clone $mine)->where('customer_wrote', 'Two mugs arrived broken')->value('reply'))->toBe('Sorry! Send us a photo please.')
        ->and((clone $mine)->whereNull('embedding')->count())->toBe(0)
        ->and(\App\Actions\Chat\ChatSession\HydrateChatReplyExamples::run()['collected'])->toBe(0);

    \App\Models\Chat\ChatReplyExample::where('organisation_id', $this->shop->organisation_id)->whereNotIn('id', (clone $mine)->pluck('id'))->delete();
    $examples = \App\Actions\Chat\ChatSession\JudgeChatSuggestion::staffExamples($this->shop, 'The vase came smashed', 1);
    expect($examples[0]['reply'])->toBe('Sorry! Send us a photo please.')
        ->and(collect(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::staffExamples($this->shop, 'The vase came smashed', 1, now()->subDay()))->pluck('reply'))->not->toContain('Sorry! Send us a photo please.');

    $session->forceDelete();
});

test('a weak suggestion is rewritten by the rewrite model from the critic notes, the best version kept, and a gap nobody needs dropped with its sentence', function () {
    config(['chat.ai_drafts' => true, 'services.openrouter.api_key' => 'or-key', 'chat.suggestion_model' => 'openai/gpt-5.6-luna', 'chat.suggestion_shop_types' => [$this->shop->type->value]]);
    Bus::fake([\App\Actions\Chat\ChatSession\ProcessChatMessageSideEffects::class, TranslateChatMessage::class]);
    fakeDraftLanguageDetection();

    $noul       = fn (float $p) => ['type' => 'noul', 'noul' => $p];
    $needsReply = 0.9;
    $covered    = 2;
    $reviews    = 0;
    \App\Actions\Helpers\AI\AskJev::mock()->shouldReceive('handle')->andReturnUsing(function (array $state, array $questions) use ($noul, &$needsReply, &$covered, &$reviews) {
        return match (true) {
            isset($questions['wants_something']) => [
                'wants_something' => $noul(0.9), 'problem' => $noul(0.97), 'about_existing_order' => $noul(0.95), 'one_question' => $noul(0.2),
                'act'             => ['type' => 'choice', 'choice' => 'asking', 'probabilities' => ['asking' => 0.9]],
                'problem_kind'    => ['type' => 'choice', 'choice' => 'damaged', 'probabilities' => ['damaged' => 0.95]],
            ],
            isset($questions['needs_reply']) => [
                'needs_reply' => $noul($needsReply), 'decision' => $noul(0.2), 'covered' => ['type' => 'score', 'score' => $covered],
                'shape'       => ['type' => 'choice', 'choice' => 'short', 'probabilities' => ['short' => 0.8]],
            ],
            isset($questions['send_as_is']) => ++$reviews < 3
                ? ['answers' => ['type' => 'score', 'score' => 1], 'invents' => $noul(0.1), 'staff_like' => ['type' => 'score', 'score' => 1], 'send_as_is' => $noul(0.2)]
                : ['answers' => ['type' => 'score', 'score' => 3], 'invents' => $noul(0.05), 'staff_like' => ['type' => 'score', 'score' => 3], 'send_as_is' => $noul(0.9)],
            isset($questions['gap0']) => ['gap0' => $noul(0.1)],
            default => null,
        };
    });

    $prompts = [];
    \App\Actions\Helpers\AI\AskToAi::shouldRun()->andReturnUsing(function (string $prompt, string $model) use (&$prompts) {
        $prompts[] = [$model, $prompt];

        return match ($model) {
            'deepseek/deepseek-v4.1-flash' => json_encode(['changes' => ['Say sorry in one line and ask for the photos.']]),
            'anthropic/claude-sonnet-5.5'  => json_encode(['topic' => 'missing_or_damaged', 'reply' => 'Sorry about the broken mugs! Could you send a photo of each one and of the box? [[check stock levels]]']),
            default                        => json_encode(['topic' => 'missing_or_damaged', 'reply' => 'Dear customer, we are very sorry to hear about this unfortunate situation with your order and the mugs.']),
        };
    });

    $session = ChatSession::create(['ulid' => (string) Str::ulid(), 'status' => ChatSessionStatusEnum::ACTIVE, 'channel' => ChatChannelEnum::WEBSITE, 'shop_id' => $this->shop->id, 'last_visitor_message_at' => now()]);
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'Two of the mugs arrived broken, what can you do?']);

    $draft    = \App\Actions\Chat\ChatSession\DraftChatReply::run($session);
    $rewrite  = collect($prompts)->firstWhere(0, 'anthropic/claude-sonnet-5.5')[1];

    expect($draft?->text)->toBe('Sorry about the broken mugs! Could you send a photo of each one and of the box?')
        ->and(data_get($draft->facts, 'model'))->toBe('anthropic/claude-sonnet-5.5')
        ->and(collect(data_get($draft->facts, 'judge.versions'))->pluck('model')->all())->toBe(['openai/gpt-5.6-luna', 'openai/gpt-5.6-luna', 'anthropic/claude-sonnet-5.5'])
        ->and(collect($prompts)->where(0, 'deepseek/deepseek-v4.1-flash')->count())->toBe(2)
        ->and(data_get($draft->facts, 'judge.gaps'))->toBe(['[[check stock levels]]' => 0.1])
        ->and($rewrite)->toContain('Say sorry in one line and ask for the photos.')
        ->and($rewrite)->toContain('Dear customer, we are very sorry')
        ->and(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::withoutSentence("Hi Ann,\nIt is in stock. [[new order?]] Thanks!", '[[new order?]]'))->toBe("Hi Ann,\nIt is in stock. Thanks!")
        ->and(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::withoutSentence('We will [[replace or credit?]] your items.', '[[replace or credit?]]'))->toBe('We will [[replace or credit?]] your items.')
        ->and(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::withoutSentence('Sorry about it. Once we have this, [[outcome]]', '[[outcome]]'))->toBe('Sorry about it.')
        ->and(\App\Actions\Chat\ChatSession\JudgeChatSuggestion::withoutSentence("Hi Anna, [[x]]\nThanks", '[[x]]'))->toBe("Hi Anna,\nThanks");

    $draft->update(['status' => \App\Enums\CRM\Livechat\ChatAiDraftStatusEnum::DISCARDED]);
    [$covered, $reviews, $prompts] = [0, -10, []];
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'And a candle holder is chipped too.']);

    expect(\App\Actions\Chat\ChatSession\DraftChatReply::run($session->refresh()))->toBeNull();

    $needsReply = 0.1;
    ChatMessage::create(['chat_session_id' => $session->id, 'message_type' => ChatMessageTypeEnum::TEXT, 'sender_type' => ChatSenderTypeEnum::GUEST, 'message_text' => 'Chat session has been closed by agent']);

    expect(\App\Actions\Chat\ChatSession\DraftChatReply::run($session->refresh()))->toBeNull();

    $session->forceDelete();
});

test('jev answers yes/no, choice and score questions through openrouter, and nothing without a key', function () {
    config()->set('services.openrouter.api_key', 'or-key');

    \Illuminate\Support\Facades\Http::fake([
        'openrouter.ai/api/alpha/decisions' => \Illuminate\Support\Facades\Http::sequence()
            ->push(['answers' => ['answer' => ['type' => 'noul', 'noul' => 0.91]]])
            ->push(['answers' => ['answer' => ['type' => 'choice', 'choice' => 'billing', 'probabilities' => ['billing' => 0.8, 'sales' => 0.2]]]])
            ->push(['answers' => ['answer' => ['type' => 'score', 'score' => 2, 'probabilities' => [0.1, 0.2, 0.7]]]])
            ->push([], 500),
    ]);

    $jev = \App\Actions\Helpers\AI\AskJev::make();

    expect($jev->noul('My payouts failed', 'Is it urgent?', 'Time-sensitive', 'No urgency'))->toBe(0.91)
        ->and($jev->choice('Refund please', 'Which team?', ['billing' => 'Payments', 'sales' => 'New accounts']))
        ->toBe(['choice' => 'billing', 'probabilities' => ['billing' => 0.8, 'sales' => 0.2]])
        ->and($jev->score('This is outrageous!', 'How frustrated?', ['Calm', 'Frustrated', 'Very angry']))
        ->toBe(['score' => 2, 'probabilities' => [0.1, 0.2, 0.7]])
        ->and($jev->noul('x', 'y', 'a', 'b'))->toBeNull();

    $sent = \Illuminate\Support\Facades\Http::recorded()->map(fn ($pair) => $pair[0]->data())->values();

    expect($sent[0]['model'])->toBe('~typesafe/jev-latest')
        ->and($sent[0]['questions']['answer'])->toBe(['type' => 'noul', 'instructions' => 'Is it urgent?', 'criteria' => ['true' => 'Time-sensitive', 'false' => 'No urgency']])
        ->and($sent[2]['questions']['answer']['criteria'])->toBe(['Calm', 'Frustrated', 'Very angry']);

    config()->set('services.openrouter.api_key', null);

    expect($jev->noul('x', 'y', 'a', 'b'))->toBeNull();
});

test('ai calls go through openrouter with the provider prefix, and straight to openai without its key', function () {
    config()->set('services.openrouter.api_key', 'or-key');
    config()->set('askbot-laravel.openai_api_key', 'openai-key');

    \Illuminate\Support\Facades\Http::fake([
        'openrouter.ai/api/v1/chat/completions' => \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => 'via openrouter']]]]),
        'api.openai.com/v1/chat/completions'    => \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => 'via openai']]]]),
    ]);

    expect(\App\Actions\Helpers\AI\AskToAi::run('hi', 'gpt-4o-mini'))->toBe('via openrouter');

    $request = \Illuminate\Support\Facades\Http::recorded()->last()[0];
    expect($request->data()['model'])->toBe('openai/gpt-4o-mini')
        ->and($request->data()['temperature'])->toBe(0.3)
        ->and($request->hasHeader('Authorization', 'Bearer or-key'))->toBeTrue();

    config()->set('services.openrouter.api_key', null);

    expect(\App\Actions\Helpers\AI\AskToAi::run('hi', 'gpt-4o-mini'))->toBe('via openai');

    $request = \Illuminate\Support\Facades\Http::recorded()->last()[0];
    expect($request->data()['model'])->toBe('gpt-4o-mini')
        ->and($request->hasHeader('Authorization', 'Bearer openai-key'))->toBeTrue();
});

test('staff flag an AI summary of an email or of a chat as wrong, saying why, once per summary', function () {
    $session = ChatSession::create([
        'ulid'     => (string) Str::ulid(),
        'status'   => ChatSessionStatusEnum::CLOSED,
        'channel'  => ChatChannelEnum::WEBSITE,
        'shop_id'  => $this->shop->id,
        'metadata' => ['ai_summary' => ['summary' => 'Customer asks about an order', 'key_points' => []]],
    ]);
    $email = ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => ChatSenderTypeEnum::USER,
        'message_text'    => 'A long email',
        'metadata'        => ['ai_summary' => 'Longer than the email itself'],
    ]);
    $plain = ChatMessage::create([
        'chat_session_id' => $session->id,
        'message_type'    => ChatMessageTypeEnum::TEXT,
        'sender_type'     => ChatSenderTypeEnum::USER,
        'message_text'    => 'Short',
    ]);

    actingAs($this->user);

    $this->postJson(route('grp.chat.ai.summaries.message.flag', [$plain->id]), ['reason' => 'x'])->assertNotFound();
    $this->postJson(route('grp.chat.ai.summaries.message.flag', [$email->id]))->assertUnprocessable()->assertJsonValidationErrors('reason');

    $this->postJson(route('grp.chat.ai.summaries.message.flag', [$email->id]), ['reason' => 'Too long'])->assertOk();
    expect(Arr::get($email->refresh()->metadata, 'ai_summary_flags.0'))->toMatchArray(['summary' => 'Longer than the email itself', 'reason' => 'Too long', 'user_id' => $this->user->id])
        ->and(\App\Http\Resources\CRM\Livechat\ChatMessageResource::make($email)->resolve())->toMatchArray(['ai_summary_flagged' => true])
        ->and(\App\Http\Resources\CRM\Livechat\ChatMessageResource::make($email)->resolve()['metadata'])->not->toHaveKey('ai_summary_flags');
    $this->postJson(route('grp.chat.ai.summaries.message.flag', [$email->id]), ['reason' => 'Again'])->assertStatus(422);

    $this->postJson(route('grp.chat.ai.summaries.session.flag', [$session->ulid]), ['reason' => 'Missed the refund'])->assertOk();
    expect(\App\Actions\Chat\UI\FlagChatAiSummary::isFlagged($session->refresh()))->toBeTrue();
    $this->postJson(route('grp.chat.ai.summaries.session.flag', [$session->ulid]), ['reason' => 'Again'])->assertStatus(422);

    $session->update(['metadata' => array_merge($session->metadata, ['ai_summary' => ['summary' => 'Customer wants a refund']])]);
    expect(\App\Actions\Chat\UI\FlagChatAiSummary::isFlagged($session->refresh()))->toBeFalse()
        ->and(Arr::get($session->metadata, 'ai_summary_flags.0.summary'))->toBe('Customer asks about an order');
    $this->postJson(route('grp.chat.ai.summaries.session.flag', [$session->ulid]), ['reason' => 'Still wrong'])->assertOk();
    expect(Arr::get($session->refresh()->metadata, 'ai_summary_flags'))->toHaveCount(2);

    $whatsappSession = MetaChatSession::create([
        'ulid'            => (string) Str::ulid(),
        'meta_channel_id' => MetaChannel::firstOrCreate(['code' => 'whatsapp'], ['name' => 'WhatsApp'])->id,
        'shop_id'         => $this->shop->id,
        'phone_number'    => '+421900003676',
        'status'          => ChatSessionStatusEnum::CLOSED,
        'language_id'     => 68,
        'priority'        => ChatPriorityEnum::NORMAL,
        'metadata'        => ['ai_summary' => ['summary' => 'Customer asks for a catalogue']],
    ]);
    $this->postJson(route('grp.chat.ai.summaries.meta_session.flag', [$whatsappSession->ulid]), ['reason' => 'They asked for prices'])->assertOk();
    expect(\App\Http\Resources\CRM\Livechat\MetaChatSessionListResource::make($whatsappSession->refresh())->resolve()['ai_summary']['flagged'])->toBeTrue();
    $whatsappSession->delete();

    $email->delete();
    $plain->delete();
    $session->delete();
});
