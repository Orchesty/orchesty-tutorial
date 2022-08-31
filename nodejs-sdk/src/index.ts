import { container, initiateContainer, listen } from '@orchesty/nodejs-sdk';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import CoreServices from '@orchesty/nodejs-sdk/dist/lib/DIContainer/CoreServices';
import MongoDbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Mongodb/Client';
import CurlSender from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/CurlSender';
import GetUsersConnector from './GetUsersConnector';
import GitHubApplication from './GitHubApplication';
import GitHubGetRepositoryConnector from './GitHubGetRepositoryConnector';
import GitHubRepositoriesBatch from './GitHubRepositoriesBatch';
import HelloWorld from './HelloWorld';
import HubSpotApplication from './HubSpotApplication';
import HubSpotCreateContactConnector from './HubSpotCreateContactConnector';
import SplitBatch from './SplitBatch';

async function prepare(): Promise<void> {
    // Load core services by:
    await initiateContainer();

    const curlSender = container.get<CurlSender>(CoreServices.CURL);
    const mongoDbClient = container.get<MongoDbClient>(CoreServices.MONGO);
    const oAuth2Provider = container.get<OAuth2Provider>(CoreServices.OAUTH2_PROVIDER);

    // Tutorial services
    const gitHubApplication = new GitHubApplication();
    container.setApplication(gitHubApplication);

    const gitHubGetRepositoryConnector = new GitHubGetRepositoryConnector()
        .setSender(curlSender)
        .setDb(mongoDbClient)
        .setApplication(gitHubApplication);
    container.setConnector(gitHubGetRepositoryConnector);

    const gitHubRepositoriesBatch = new GitHubRepositoriesBatch()
        .setSender(curlSender)
        .setDb(mongoDbClient)
        .setApplication(gitHubApplication);
    container.setBatch(gitHubRepositoriesBatch);

    const hubSpotApplication = new HubSpotApplication(oAuth2Provider);
    container.setApplication(hubSpotApplication);

    const hubSpotCreateContactConnector = new HubSpotCreateContactConnector()
        .setSender(curlSender)
        .setDb(mongoDbClient)
        .setApplication(hubSpotApplication);
    container.setConnector(hubSpotCreateContactConnector);

    const getUsers = new GetUsersConnector()
        .setSender(curlSender);
    container.setConnector(getUsers);

    container.setCustomNode(new HelloWorld());
    container.setBatch(new SplitBatch());
    // Tutorial services end
}

// Start App by:
// eslint-disable-next-line @typescript-eslint/no-floating-promises
prepare().then(listen);
