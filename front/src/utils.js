export default {
  convertFromNano(amountNano, noFormat = false) {
    const amountBigInt = BigInt(amountNano);
    const integer = amountBigInt / BigInt('1000000000');
    return noFormat ? integer.toString() : integer.toLocaleString();
  },
}
