import { AUTHORIZATION_FORM } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/AApplication';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import FormStack from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FormStack';
import {
    ABasicApplication,
    PASSWORD,
    USER,
} from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import { parseHttpMethod } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import AProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/AProcessDto';
import { encode } from '@orchesty/nodejs-sdk/dist/lib/Utils/Base64';
import { CommonHeaders, JSON_TYPE } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';

export default class SendgridApplication extends ABasicApplication {

    public getDescription(): string {
        return 'SendgridApplication';
    }

    public getName(): string {
        return 'sendgrid';
    }

    public getPublicName(): string {
        return 'Sendgrid';
    }

    public getRequestDto(
        dto: AProcessDto,
        applicationInstall: ApplicationInstall,
        method: string,
        url?: string,
        data?: string,
    ): RequestDto {
        if (!this.isAuthorized(applicationInstall)) {
            throw new Error('Missing authorization settings');
        }

        const settings = applicationInstall.getSettings();
        const token = encode(`${settings[AUTHORIZATION_FORM][USER]}:${settings[AUTHORIZATION_FORM][PASSWORD]}`);
        const headers = {
            [CommonHeaders.AUTHORIZATION]: `Basic ${token}`,
            [CommonHeaders.ACCEPT]: JSON_TYPE,
            [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
        };

        return new RequestDto(url ?? '', parseHttpMethod(method), dto, data, headers);
    }

    public getFormStack(): FormStack {
        const form = new Form(AUTHORIZATION_FORM, 'Authorization settings');
        form
            .addField(new Field(FieldType.TEXT, USER, 'Username', undefined, true))
            .addField(new Field(FieldType.PASSWORD, PASSWORD, 'Password', undefined, true));

        return new FormStack().addForm(form);
    }

}
