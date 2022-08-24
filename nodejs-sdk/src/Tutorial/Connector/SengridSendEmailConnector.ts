import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import SendgridApplication from '../SendgridApplication';

export default class SengridSendEmailConnector extends AConnector {

    public getName(): string {
        return 'sendgrid-send-email';
    }

    public async processAction(dto: ProcessDto<IInput>): Promise<ProcessDto> {
        const data = dto.getJsonData();

        const appInstall = await this.getApplicationInstallFromProcess(dto);
        const body = {
            personalizations: [
                {
                    to: [{ email: data.target }],
                    from: { email: 'email@no-reply.com' },
                    subject: 'Email subject',
                    content: [{
                        type: 'text/plain',
                        value: data.content,
                    }],
                },
            ],
        };

        const request = this.getApplication<SendgridApplication>()
            .getRequestDto(dto, appInstall, HttpMethods.POST, 'https://api.sendgrid.com/v3/mail/send', JSON.stringify(body));
        await this.getSender().send(request);

        return dto;
    }

}

interface IInput {
    target: string;
    content: string;
}
