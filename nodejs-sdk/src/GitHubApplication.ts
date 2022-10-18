import CoreFormsEnum from '@orchesty/nodejs-sdk/dist/lib/Application/Base/CoreFormsEnum';
import { IWebhookApplication } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/IWebhookApplication';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import Webhook from '@orchesty/nodejs-sdk/dist/lib/Application/Database/Webhook';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import FormStack from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FormStack';
import WebhookSubscription from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Webhook/WebhookSubscription';
import {
    ABasicApplication,
    TOKEN,
} from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import ResponseDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/ResponseDto';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import AProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/AProcessDto';
import { CommonHeaders, JSON_TYPE } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export const NAME = 'git-hub';

export default class GitHubApplication extends ABasicApplication implements IWebhookApplication {

    public getName(): string {
        return NAME;
    }

    public getPublicName(): string {
        return 'GitHub';
    }

    public getDescription(): string {
        return 'Service that helps developers store and manage their code, as well as track and control changes to their code';
    }

    public getFormStack(): FormStack {
        const form = new Form(CoreFormsEnum.AUTHORIZATION_FORM, 'Authorization settings')
            .addField(new Field(FieldType.TEXT, TOKEN, ' Token', undefined, true));

        return new FormStack().addForm(form);
    }

    public isAuthorized(applicationInstall: ApplicationInstall): boolean {
        const authorizationForm = applicationInstall.getSettings()[CoreFormsEnum.AUTHORIZATION_FORM];
        return authorizationForm?.[TOKEN];
    }

    public getRequestDto(
        dto: AProcessDto,
        applicationInstall: ApplicationInstall,
        method: HttpMethods,
        uri?: string,
        data?: unknown,
    ): RequestDto {
        const request = new RequestDto(`https://api.github.com${uri}`, method, dto);
        if (!this.isAuthorized(applicationInstall)) {
            throw new Error(`Application [${this.getPublicName()}] is not authorized!`);
        }
        const form = applicationInstall.getSettings()[CoreFormsEnum.AUTHORIZATION_FORM] ?? {};
        request.setHeaders({
            [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
            [CommonHeaders.ACCEPT]: 'application/vnd.github+json',
            [CommonHeaders.AUTHORIZATION]: `Bearer ${form[TOKEN]}`,
        });

        if (data) {
            request.setJsonBody(data);
        }

        return request;
    }

    public getWebhookSubscribeRequestDto(
        applicationInstall: ApplicationInstall,
        subscription: WebhookSubscription,
        url: string,
    ): RequestDto {
        const request = new ProcessDto();
        const { owner, record } = subscription.getParameters();
        return this.getRequestDto(
            request,
            applicationInstall,
            HttpMethods.POST,
            `repos/${owner}/${record}/hooks`,
            JSON.stringify({
                config: {
                    url,
                    // eslint-disable-next-line @typescript-eslint/naming-convention
                    content_type: 'json',
                },
                name: 'web',
                events: [subscription.getName()],
            }),
        );
    }

    public getWebhookSubscriptions(): WebhookSubscription[] {
        return [
            new WebhookSubscription('issues', '', '', { record: 'record', owner: 'owner' }),
            new WebhookSubscription('pull-request', '', '', { record: 'record', owner: 'owner' }),
        ];
    }

    public getWebhookUnsubscribeRequestDto(applicationInstall: ApplicationInstall, webhook: Webhook): RequestDto {
        const webhookSubscription = this.getWebhookSubscriptions().filter(
            (item) => item.getName() === webhook.getName(),
        )[0];
        const { repository, owner } = webhookSubscription.getParameters();

        const request = new ProcessDto();
        return this.getRequestDto(
            request,
            applicationInstall,
            HttpMethods.DELETE,
            `repos/${owner}/${repository}/hooks/${webhook.getWebhookId()}`,
        );
    }

    public processWebhookSubscribeResponse(
        dto: ResponseDto,
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        applicationInstall: ApplicationInstall,
    ): string {
        return (dto.getJsonBody() as { id: string }).id;
    }

    public processWebhookUnsubscribeResponse(dto: ResponseDto): boolean {
        return dto.getResponseCode() === 204;
    }

}
