pragma solidity >=0.6.0;

contract Names {
    mapping(address => bytes) list;

    modifier alwaysAccept {
        tvm.accept();
        _;
    }

    modifier onlyInternalMessage {
        require(msg.sender != address(0));
        tvm.accept();
        _;
    }

    function setName(bytes name) public onlyInternalMessage {
        list[msg.sender] = name;
        msg.sender.transfer(0, false, 2 | 64);
    }

    function clear() public onlyInternalMessage {
        delete list[msg.sender];
        msg.sender.transfer(0, false, 2 | 64);
    }

    function getList() public view alwaysAccept returns (mapping(address => bytes)) {
        return list;
    }
}
