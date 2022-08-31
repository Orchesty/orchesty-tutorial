import { AUTHORIZATION_FORM } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/AApplication';
import ApplicationTypeEnum from '@orchesty/nodejs-sdk/dist/lib/Application/Base/ApplicationTypeEnum';
import { IWebhookApplication } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/IWebhookApplication';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import FormStack from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FormStack';
import WebhookSubscription from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Webhook/WebhookSubscription';
import AOAuth2Application from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/OAuth2/AOAuth2Application';
import { CLIENT_ID, CLIENT_SECRET } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/OAuth2/IOAuth2Application';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import ResponseDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/ResponseDto';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import AProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/AProcessDto';
import { CommonHeaders, JSON_TYPE } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import { BodyInit, Headers } from 'node-fetch';

const APP_ID = 'app_id';
export const BASE_URL = 'https://api.hubapi.com';

export default class HubSpotApplication extends AOAuth2Application implements IWebhookApplication {

    public getApplicationType(): ApplicationTypeEnum {
        return ApplicationTypeEnum.WEBHOOK;
    }

    public getName(): string {
        return 'hub-spot';
    }

    public getPublicName(): string {
        return 'HubSpot';
    }

    public getAuthUrl(): string {
        return 'https://app.hubspot.com/oauth/authorize';
    }

    public getTokenUrl(): string {
        return 'https://api.hubapi.com/oauth/v1/token';
    }

    public getDescription(): string {
        return 'HubSpot application with OAuth 2';
    }

    public getRequestDto(
        dto: AProcessDto,
        applicationInstall: ApplicationInstall,
        method: HttpMethods,
        url?: string,
        data?: BodyInit,
    ): RequestDto {
        const headers = new Headers({
            [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
            [CommonHeaders.ACCEPT]: JSON_TYPE,
            [CommonHeaders.AUTHORIZATION]: `Bearer ${this.getAccessToken(applicationInstall)}`,
        });

        return new RequestDto(url ?? BASE_URL, method, dto, data, headers);
    }

    public getFormStack(): FormStack {
        const form = new Form(AUTHORIZATION_FORM, 'Authorization settings')
            .addField(new Field(FieldType.TEXT, CLIENT_ID, 'Client Id', null, true))
            .addField(new Field(FieldType.TEXT, CLIENT_SECRET, 'Client Secret', null, true))
            .addField(new Field(FieldType.TEXT, APP_ID, 'Application Id', null, true));

        return new FormStack().addForm(form);
    }

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    public getScopes(applicationInstall: ApplicationInstall): string[] {
        return ['contacts'];
    }

    public getWebhookSubscriptions(): WebhookSubscription[] {
        return [
            new WebhookSubscription('Create Contact', 'Webhook', '', { name: 'contact.creation' }),
            new WebhookSubscription('Delete Contact', 'Webhook', '', { name: 'contact.deletion' }),
        ];
    }

    public getWebhookSubscribeRequestDto(
        applicationInstall: ApplicationInstall,
        subscription: WebhookSubscription,
        url: string,
    ): RequestDto {
        const hubspotUrl = `${BASE_URL}/webhooks/v1/${applicationInstall.getSettings()[AUTHORIZATION_FORM][APP_ID]}`;
        const body = JSON.stringify({
            webhookUrl: url,
            subscriptionDetails: {
                subscriptionType: subscription.getParameters().name,
                propertyName: 'email',
            },
            enabled: false,
        });

        return this.getRequestDto(new ProcessDto(), applicationInstall, HttpMethods.POST, hubspotUrl, body);
    }

    public getWebhookUnsubscribeRequestDto(applicationInstall: ApplicationInstall, id: string): RequestDto {
        const url = `${BASE_URL}/webhooks/v1/${applicationInstall
            .getSettings()[AUTHORIZATION_FORM][APP_ID]}/subscriptions/${id}`;

        return this.getRequestDto(new ProcessDto(), applicationInstall, HttpMethods.DELETE, url);
    }

    public processWebhookSubscribeResponse(
        dto: ResponseDto<{ id: string }>,
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        applicationInstall: ApplicationInstall,
    ): string {
        const jsonBody = dto.getJsonBody();

        return jsonBody.id ?? '';
    }

    public processWebhookUnsubscribeResponse(dto: ResponseDto): boolean {
        return dto.getResponseCode() === 204;
    }

}