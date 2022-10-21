import { EventEnum } from '@orchesty/nodejs-connectors/dist/lib/Common/Events/EventEnum';
import EventStatusFilter from '@orchesty/nodejs-connectors/dist/lib/Common/EventStatusFilter/EventStatusFilter';
import { container, initiateContainer } from '@orchesty/nodejs-sdk';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import CoreServices from '@orchesty/nodejs-sdk/dist/lib/DIContainer/CoreServices';
import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import MongoDbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Mongodb/Client';
import CurlSender from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/CurlSender';
import GetUsersConnector from './GetUsersConnector';
import GitHubApplication from './GitHubApplication';
import GitHubGetRepositoryConnector from './GitHubGetRepositoryConnector';
import GitHubRepositoriesBatch from './GitHubRepositoriesBatch';
import GitHubStoreRepositoriesBatch from './GitHubStoreRepositoriesBatch';
import HelloWorld from './HelloWorld';
import HubSpotApplication from './HubSpotApplication';
import HubSpotCreateContactConnector from './HubSpotCreateContactConnector';
import LoadRepositories from './LoadRepositories';
import SplitBatch from './SplitBatch';

export default async function prepare(): Promise<void> {
    // Load core services by:
    await initiateContainer();

    const curlSender = container.get<CurlSender>(CoreServices.CURL);
    const mongoDbClient = container.get<MongoDbClient>(CoreServices.MONGO);
    const oAuth2Provider = container.get<OAuth2Provider>(CoreServices.OAUTH2_PROVIDER);

    const dataStorageManager = new DataStorageManager(mongoDbClient);
    container.set(CoreServices.DATA_STORAGE_MANAGER, dataStorageManager);

    // System event services
    const eventStatusFilterSuccess = new EventStatusFilter(EventEnum.PROCESS_SUCCESS);
    container.setCustomNode(eventStatusFilterSuccess);

    const eventStatusFilterError = new EventStatusFilter(EventEnum.PROCESS_FAILED);
    container.setCustomNode(eventStatusFilterError);

    const eventStatusFilterLimiter = new EventStatusFilter(EventEnum.LIMIT_OVERFLOW);
    container.setCustomNode(eventStatusFilterLimiter);

    const eventStatusFilterTrash = new EventStatusFilter(EventEnum.MESSAGE_IN_TRASH);
    container.setCustomNode(eventStatusFilterTrash);

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

    const gitHubStoreRepositoriesBatch = new GitHubStoreRepositoriesBatch(dataStorageManager)
        .setSender(curlSender)
        .setDb(mongoDbClient)
        .setApplication(gitHubApplication);
    container.setBatch(gitHubStoreRepositoriesBatch);

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

    container.setCustomNode(new LoadRepositories(dataStorageManager));

    container.setCustomNode(new HelloWorld());
    container.setBatch(new SplitBatch());
    // Tutorial services end
}
