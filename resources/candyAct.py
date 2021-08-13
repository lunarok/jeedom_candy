import requests
import subprocess,asyncio,json,time,argparse,re

parser = argparse.ArgumentParser()
parser.add_argument("ip", help="ip")
parser.add_argument("key", help="key")
parser.add_argument("command", help="command")
args = parser.parse_args()

status = requests.get("http://" + args.ip + "/http-write.json?encrypted=0" + args.command).text

print(status)
