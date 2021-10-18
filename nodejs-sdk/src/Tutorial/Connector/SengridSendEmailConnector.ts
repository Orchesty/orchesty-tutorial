import AConnector from 'pipes-nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';
import HttpMethods from 'pipes-nodejs-sdk/dist/lib/Transport/HttpMethods';

export default class SengridSendEmailConnector extends AConnector {

  public getName = () => 'sendgrid-send-email';

  public async processAction(dto: ProcessDto): Promise<ProcessDto> {
    const data = dto.jsonData as IInput;

    const appInstall = await this._getApplicationInstall();
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

    const request = await this._application.getRequestDto(dto, appInstall, HttpMethods.POST, 'https://api.sendgrid.com/v3/mail/send', JSON.stringify(body));
    await this._sender.send(request);

    return dto;
  }

}

interface IInput {
  target: string
  content: string
}
