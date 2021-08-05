import requests
import subprocess,asyncio,json,time,argparse,re

parser = argparse.ArgumentParser()
parser.add_argument("ip", help="ip")

def getkey():
	input = requests.get("http://" + args.ip + "/http-write.json?encrypted=1&BM=1").text
	response = '{"response":"SUCCESS"}'

	return "".join([chr(ord(response[i]) ^ int(input[i*2:i*2+2], 16)) for i in range(0, 16)])

def decode(uri):
	status = requests.get("http://" + args.ip + "/" + uri).text

	return "".join([chr(ord(key[idx % len(key)]) ^ int(status[i:i+2], 16)) for idx,i in enumerate(range(0, len(status), 2))])

key = getkey()

print(key)
print(decode("http-read.json?encrypted=1"))
print(decode("http-getStatistics.json?encrypted=1"))
