# ReadNFC
# Thomas Baer

import httplib
import time
import re

import nxppy
import RPi.GPIO as GPIO


server = 'sldev.fritz.box'
uri = '/info.php?uid='
GREEN = 47
RED = 35
ON = GPIO.HIGH
OFF = GPIO.LOW


def blinkled(led, duration):
    GPIO.output(led, ON)
    time.sleep(duration)
    GPIO.output(led, OFF)


def blinkboth(duration):
    GPIO.output(RED, ON)
    GPIO.output(GREEN, ON)
    time.sleep(duration)
    GPIO.output(RED, OFF)
    GPIO.output(GREEN, OFF)

GPIO.setwarnings(False)
GPIO.setmode(GPIO.BCM)
GPIO.setup(GREEN, GPIO.OUT)
GPIO.setup(RED, GPIO.OUT)
GPIO.output(RED, OFF)
GPIO.output(GREEN, OFF)

mifare = nxppy.Mifare()
uid1 = ''
uid2 = ''
while True:
    blinkled(RED, 0.1)
    time.sleep(1)
    try:
        uid1 = mifare.select()
        blinkboth(0.2)
        if uid1 != uid2 and uid1 is not None:
            conn = httplib.HTTPConnection(server, 80, timeout=5)
            conn.request("GET", uri + uid1)
            r = conn.getresponse()
            if (r.status == 200):
                blinkled(GREEN, 1)
                if re.match('\d', r.reason) is not None:
                    for i in range(0, r.reason):
                        blinkled(RED, 0.2)
            else:
                blinkled(RED, 0.2)
                time.sleep(0.2)
                blinkled(RED, 0.2)
                time.sleep(0.2)
                blinkled(RED, 0.2)
                time.sleep(0.2)
            conn.close()
            time.sleep(5)
        uid2 = uid1
    except nxppy.SelectError:
        pass