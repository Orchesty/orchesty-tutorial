import { initiateContainer, listen } from 'pipes-nodejs-sdk';

const prepare = async (): Promise<void> => {
// Load core services by:
    await initiateContainer();

    // Express.js is available by import:
    // import { expressApp } from 'pipes-nodejs-sdk/lib';

    // DIContainer is available by import:
    // import { container } from 'pipes-nodejs-sdk/lib';

    // How to add Connector to the DIC
    // const myConnector = new MyConnector()
    // container.setConnector(myConnector);

    // How to add CustomNode to the DIC
    // const myCustomNode = new MyCustomNode()
    // container.setCustomNode(myCustomNode);

    // How to add Batch to the DIC
    // const myBatch = new MyBatch()
    // container.setBatch(myBatch);

// How to add Application to the DIC
// const myApp = new MyApp()
// container.setApplication(myApp);
};

// Start App by:
prepare().then(listen);
