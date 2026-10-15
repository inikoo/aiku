<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerEmailTemplate;

use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Every shop starts with the same ten emails, written in English by us and translated into the
 * shop's language in the background. The shop's staff then edit their own copies.
 */
class SeedShopCustomerEmailTemplates
{
    use AsAction;

    public const array DEFAULTS = [
        [
            'name'    => 'Open basket: can I help?',
            'subject' => 'Your basket at {shop_name} is waiting',
            'body'    => "Hello {contact_name},\n\nI noticed you still have some items in your basket with us:\n\n{basket}\n\nBasket total: {basket_total}\n\nIs there anything I can help with before you place the order? If you have a question about a product, stock or delivery, just reply to this email.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Open basket: stock is not reserved',
            'subject' => 'The items in your basket are selling fast',
            'body'    => "Hello {contact_name},\n\nJust a friendly note: items in a basket are not reserved, and some of the products you picked are popular lines that can sell out quickly:\n\n{basket}\n\nIf you would like to secure them, you can complete your order at {website}.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Open basket: anything stopping you?',
            'subject' => 'Can I help you complete your order?',
            'body'    => "Hello {contact_name},\n\nI saw that your order with us has not been placed yet. If something is holding you back, such as payment, delivery times or a product you could not find, let me know and I will sort it out for you.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'We miss you',
            'subject' => 'We have not seen you for a while, {contact_name}',
            'body'    => "Hello {contact_name},\n\nIt has been a while since your last order, and we wanted to say hello. We have added a lot of new products since then, and your favourite lines are back in stock.\n\nHave a look at {website}, and if there is anything I can do for {company_name}, just reply to this email.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'New arrivals',
            'subject' => 'New arrivals at {shop_name}',
            'body'    => "Hello {contact_name},\n\nWe have just added new products that I think would sell well in your shop. You can see them at {website}.\n\nIf you would like recommendations for {company_name}, reply to this email and I will put a selection together for you.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Thank you for your order',
            'subject' => 'Thank you for your order',
            'body'    => "Hello {contact_name},\n\nThank you for your recent order with {shop_name}. We really appreciate your business.\n\nIf you need anything at all, such as product information, images for your website or help with your next order, I am here to help.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Welcome',
            'subject' => 'Welcome to {shop_name}',
            'body'    => "Hello {contact_name},\n\nWelcome to {shop_name}! My name is {my_name}, and I will be happy to help you with anything you need.\n\nYou can browse our full range at {website}. If you have any questions about products, ordering or delivery, simply reply to this email.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Stock up for the season',
            'subject' => 'Time to stock up for the busy season',
            'body'    => "Hello {contact_name},\n\nThe busy season is coming, and now is a good time to stock up before our best sellers run low.\n\nYou can order at {website}. If you would like help choosing, reply to this email and I will suggest the products that sell best for shops like yours.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'How was your order?',
            'subject' => 'How was your last order?',
            'body'    => "Hello {contact_name},\n\nI hope your last order arrived safely and that everything was as expected.\n\nWe would love to hear what you think. If anything was not right, please tell me and I will make sure it is put right.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
        [
            'name'    => 'Just checking in',
            'subject' => 'Just checking in',
            'body'    => "Hello {contact_name},\n\nI wanted to check in and see how things are going at {company_name}. Is there anything you need from us, or any product you are looking for that you cannot find?\n\nJust reply to this email, and I will be happy to help.\n\nKind regards,\n{my_name}\n{shop_name}",
        ],
    ];

    public function handle(Shop $shop): void
    {
        if ($shop->customerEmailTemplates()->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $position => $template) {
            $shop->customerEmailTemplates()->create(array_merge($template, [
                'group_id'        => $shop->group_id,
                'organisation_id' => $shop->organisation_id,
                'position'        => $position + 1,
            ]));
        }

        if ($shop->language->code !== 'en') {
            TranslateShopCustomerEmailTemplates::dispatch($shop);
        }
    }
}
