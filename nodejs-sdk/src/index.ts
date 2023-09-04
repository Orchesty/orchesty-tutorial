import { EventEnum } from '@orchesty/nodejs-connectors/dist/lib/Common/Events/EventEnum';
import EventStatusFilter from '@orchesty/nodejs-connectors/dist/lib/Common/EventStatusFilter/EventStatusFilter';
import { container, initiateContainer } from '@orchesty/nodejs-sdk';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import FileSystemClient from '@orchesty/nodejs-sdk/dist/lib/Storage/File/FileSystem';
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
    const oAuth2Provider = container.get(OAuth2Provider);

    const dataStorageManager = new DataStorageManager(fileSystemClient);
    container.set(dataStorageManager);

    // System event services
    container.setNode(new EventStatusFilter(EventEnum.PROCESS_SUCCESS));
    container.setNode(new EventStatusFilter(EventEnum.PROCESS_FAILED));
    container.setNode(new EventStatusFilter(EventEnum.LIMIT_OVERFLOW));
    container.setNode(new EventStatusFilter(EventEnum.MESSAGE_IN_TRASH));

    // Tutorial services
    const gitHubApplication = new GitHubApplication();
    container.setApplication(gitHubApplication);
    container.setNode(new GitHubGetRepositoryConnector(), gitHubApplication);
    container.setNode(new GitHubRepositoriesBatch(), gitHubApplication);
    container.setNode(new GitHubStoreRepositoriesBatch(dataStorageManager), gitHubApplication);

    const hubSpotApplication = new HubSpotApplication(oAuth2Provider);
    container.setApplication(hubSpotApplication);
    container.setNode(new HubSpotCreateContactConnector(), hubSpotApplication);

    container.setNode(new GetUsersConnector());
    container.setNode(new LoadRepositories(dataStorageManager));
    container.setNode(new HelloWorld());
    container.setNode(new SplitBatch());
    // Tutorial services end
}
