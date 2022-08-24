import { container, initiateContainer, listen } from '@orchesty/nodejs-sdk';
import CoreServices from '@orchesty/nodejs-sdk/dist/lib/DIContainer/CoreServices';
import MongoDbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Mongodb/Client';
import CurlSender from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/CurlSender';
import CursorBatch from './Tutorial/Batch/CursorBatch';
import SplitBatch from './Tutorial/Batch/SplitBatch';
import GetUsersConnector from './Tutorial/Connector/GetUsersConnector';
import SengridSendEmailConnector from './Tutorial/Connector/SengridSendEmailConnector';
import { CustomNode } from './Tutorial/CustomNode/CustomNode';
import SendgridApplication from './Tutorial/SendgridApplication';

async function prepare(): Promise<void> {
    // Load core services by:
    await initiateContainer();

    const curlSender = container.get<CurlSender>(CoreServices.CURL);
    const mongoDbClient = container.get<MongoDbClient>(CoreServices.MONGO);

    // Tutorial services
    const getUsers = new GetUsersConnector()
        .setSender(curlSender);
    container.setConnector(getUsers);
    container.setCustomNode(new CustomNode());

    const sendgridApp = new SendgridApplication();
    container.setApplication(sendgridApp);

    const sendgridConn = new SengridSendEmailConnector();
    sendgridConn
        .setSender(curlSender)
        .setDb(mongoDbClient)
        .setApplication(sendgridApp);

    container.setBatch(new SplitBatch());
    container.setBatch(new CursorBatch());
    // Tutorial services end
}

// Start App by:
// eslint-disable-next-line @typescript-eslint/no-floating-promises
prepare().then(listen);
