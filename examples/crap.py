import socket
import msgpack

def make_register_rpc(id: str, sdk: str) -> dict:
    return {
        'Version': 1,
        'OpCode': 0x00,
        'Args': [id, sdk],
    }

def make_treatment_rpc(key: str, feature: str) -> dict:
    return {
        'Version': 1,
        'OpCode': 0x11,
        'Args': [key, None, feature, {}],
    }

def send_rpc(sock: socket.socket, rpc: dict):
    serialized = msgpack.packb(rpc)
    size = len(serialized).to_bytes(4, byteorder='little')
    sock.send(size)
    sock.send(serialized)

def receive_response(sock: socket.socket) -> object:
    raw = sock.recv(4)
    int.from_bytes(raw, byteorder='little')
    raw = sock.recv(500)
    return msgpack.unpackb(raw)

def main():
    sock = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
    try:
        sock.connect('../../splitd/splitd.sock')
        send_rpc(sock, make_register_rpc('pepito', 'rust-0.0.1'))
        print(receive_response(sock))
        send_rpc(sock, make_treatment_rpc('pablo', 'feature_pindonga'))
        print(receive_response(sock))
    finally:
        sock.close()



if __name__ == '__main__':
    main()
