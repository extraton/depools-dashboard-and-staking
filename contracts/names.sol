pragma solidity >=0.6.0;

contract Names {
    struct Item {
        address msigAddress;
        address depoolAddress;
        bytes name;
    }

    Item[] items;

    modifier alwaysAccept {
        tvm.accept();
        _;
    }

    modifier onlyInternalMessage {
        require(msg.sender != address(0));
        tvm.accept();
        _;
    }

    modifier acceptOnlyOwner {
        require(msg.pubkey() == tvm.pubkey(), 101);
        tvm.accept();
        _;
    }

    function setName(address depoolAddress, bytes name) public onlyInternalMessage {
        items.push(Item(msg.sender, depoolAddress, name));
        msg.sender.transfer(0, false, 2 | 64);
    }

    function getList() public view alwaysAccept returns (Item[]) {
        return items;
    }

    function clear() public acceptOnlyOwner {
        delete items;
    }
}
