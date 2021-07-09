import {TONClient} from "ton-client-web-js";
import freeton from "freeton";
import semver from "semver";
import BigNumber from 'bignumber.js';

const depoolAbi = require('@/contracts/DePool.abi.json');

const _ = {
  client: null,
  async getClient() {
    if (null === this.client) {
      this.client = await TONClient.create({servers: ['main2.ton.dev']});
    }
    return this.client;
  }
};

export default {
  transactionAdditionalFee: '500000000',
  convertFromNano(amountNano, decimalNum = null) {
    return new BigNumber(amountNano).dividedBy(new BigNumber('1000000000')).toFormat(decimalNum);
  },
  convertFromNanoToInt(amountNano, direction) {
    return new BigNumber(amountNano).dividedBy(new BigNumber('1000000000')).integerValue(direction);
  },
  formatNumber(number) {
    return new BigNumber(number).toFormat();
  },
  convertToNano(amount) {
    return (BigInt(amount) * BigInt('1000000000')).toString();
  },
  async generateMessage(abi, functionName, params = {}) {
    const client = await _.getClient();
    return client.contracts.createRunBody({
      abi,
      function: functionName,
      params,
      internal: true
    });
  },
  async sendTransactionToDepool(provider, address, functionName, params, amount) {
    const message = await this.generateMessage(depoolAbi, functionName, params);
    const signer = await provider.getSigner();
    const wallet = signer.getWallet();
    const contractMessageProcessing = await wallet.transfer(address, amount, true, message.bodyBase64);
    await contractMessageProcessing.wait();
  },
  async isExtensionAvailableWithMinimalVersion() {
    return new Promise(resolve => {
      if (typeof window.freeton === 'undefined') {
        resolve(false);
      }
      const provider = new freeton.providers.ExtensionProvider(window.freeton);
      provider.getVersion().then(data => {
        const currentVersion = data.version || '0.0.0';
        resolve(semver.satisfies(currentVersion, '>=0.4.1'));
      }).catch(() => {
        resolve(false);
      });
    });
  }
}
