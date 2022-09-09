<?php


namespace App\Actions\SysAdmin\Profile;

use App\Actions\UpdateModelAction;
use App\Http\Resources\Utils\ActionResultResource;
use App\Models\SysAdmin\User;
use App\Models\Utils\ActionResult;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property \App\Models\SysAdmin\User $user
 */
class UpdateProfile extends UpdateModelAction
{
    use AsAction;

    public function handle(User $user, array $modelData): ActionResult
    {
        $this->model     = $user;
        $this->modelData = $modelData;
        return $this->updateAndFinalise(jsonFields: ['data', 'settings']);
    }


    public function rules(): array
    {
        return [
            'username' => 'sometimes|required|alpha_dash|unique:App\Models\SysAdmin\User,username',
            'password' => ['sometimes', 'required', Password::min(8)->uncompromised()],
            'language' => 'sometimes|required|exists:languages,code'
        ];
    }


    public function asController(ActionRequest $request): ActionResult
    {

        $validated = $request
            ->validatedShiftToArray([
                                        'language' => 'settings'
                                    ]);


        return $this->handle($request->user(), $validated);

    }

    public function HtmlResponse(): \Illuminate\Http\RedirectResponse
    {
        return Redirect::route('welcome');
    }

    public function JsonResponse(ActionResult $actionResult): ActionResultResource
    {

        return new ActionResultResource($actionResult);
    }

}
