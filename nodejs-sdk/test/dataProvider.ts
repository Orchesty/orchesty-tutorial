import { AUTHORIZATION_FORM } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/AApplication';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import { ACCESS_TOKEN } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import { TOKEN } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import { NAME as GitHubApplicationName } from '../src/GitHubApplication';
import { NAME as HubSpotApplicationName } from '../src/HubSpotApplication';
import { container } from './TestAbstract';

export async function prepareApplications(): Promise<void> {
    const repo = container.getRepository(ApplicationInstall);

    const githubApp = new ApplicationInstall();
    githubApp
        .setEnabled(true)
        .setName(GitHubApplicationName)
        .setUser('user');
    await repo.insert(githubApp);

    const hubspotApp = new ApplicationInstall();
    hubspotApp
        .setEnabled(true)
        .setName(HubSpotApplicationName)
        .setUser('user')
        .setSettings({
            [AUTHORIZATION_FORM]: {
                [TOKEN]: {
                    [ACCESS_TOKEN]: 'tkn',
                },
            },
        });
    await repo.insert(hubspotApp);
}
