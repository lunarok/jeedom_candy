import requests
import subprocess,asyncio,json,time,argparse,re

parser = argparse.ArgumentParser()
parser.add_argument("ip", help="ip")
parser.add_argument("key", help="key")
parser.add_argument("command", help="command")
args = parser.parse_args()

def getkey():
	input = requests.get("http://" + args.ip + "/http-write.json?encrypted=1&BM=1").text
	response = '{"response":"SUCCESS"}'

	return "".join([chr(ord(response[i]) ^ int(input[i*2:i*2+2], 16)) for i in range(0, 16)])

def decode(uri):
	status = requests.get("http://" + args.ip + "/" + uri).text

	return "".join([chr(ord(args.key[idx % len(args.key)]) ^ int(status[i:i+2], 16)) for idx,i in enumerate(range(0, len(status), 2))])

if args.command == 'key' :
	print(getkey())
if args.command == 'status' :
	print(decode("http-read.json?encrypted=1"))
if args.command == 'stats' :
	print(decode("http-getStatistics.json?encrypted=1"))
