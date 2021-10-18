import { container, initiateContainer, listen } from 'pipes-nodejs-sdk';
import CoreServices from 'pipes-nodejs-sdk/dist/lib/DIContainer/CoreServices';
import GetUsersConnector from './Tutorial/Connector/GetUsersConnector';
import SendgridApplication from './Tutorial/SendgridApplication';
import SengridSendEmailConnector from './Tutorial/Connector/SengridSendEmailConnector';
import { CustomNode } from './Tutorial/CustomNode/CustomNode';
import { SplitBatch } from './Tutorial/Batch/SplitBatch';
import { CursorBatch } from './Tutorial/Batch/CursorBatch';

const prepare = async (): Promise<void> => {
  // Load core services by:
  await initiateContainer();

  const curlSender = container.get(CoreServices.CURL);
  const mongoDbClient = container.get(CoreServices.MONGO);

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
};

// Start App by:
prepare().then(listen);
