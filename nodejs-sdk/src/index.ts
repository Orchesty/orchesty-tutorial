import { EventEnum } from '@orchesty/nodejs-connectors/dist/lib/Common/Events/EventEnum';
import EventStatusFilter from '@orchesty/nodejs-connectors/dist/lib/Common/EventStatusFilter/EventStatusFilter';
import { container, initiateContainer } from '@orchesty/nodejs-sdk';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import DbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Database/Client';
import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import FileSystemClient from '@orchesty/nodejs-sdk/dist/lib/Storage/File/FileSystem';
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

export default function prepare(): void {
    // Load core services by:
    initiateContainer();

    const fileSystemClient = new FileSystemClient();
    const curlSender = container.get(CurlSender);
    const oAuth2Provider = container.get(OAuth2Provider);
    const databaseClient = container.get(DbClient);

    const dataStorageManager = new DataStorageManager(fileSystemClient);
    container.set(dataStorageManager);

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
        .setDb(databaseClient)
        .setApplication(gitHubApplication);
    container.setConnector(gitHubGetRepositoryConnector);

    const gitHubRepositoriesBatch = new GitHubRepositoriesBatch()
        .setSender(curlSender)
        .setDb(databaseClient)
        .setApplication(gitHubApplication);
    container.setBatch(gitHubRepositoriesBatch);

    const gitHubStoreRepositoriesBatch = new GitHubStoreRepositoriesBatch(dataStorageManager)
        .setSender(curlSender)
        .setDb(databaseClient)
        .setApplication(gitHubApplication);
    container.setBatch(gitHubStoreRepositoriesBatch);

    const hubSpotApplication = new HubSpotApplication(oAuth2Provider);
    container.setApplication(hubSpotApplication);

    const hubSpotCreateContactConnector = new HubSpotCreateContactConnector()
        .setSender(curlSender)
        .setDb(databaseClient)
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
