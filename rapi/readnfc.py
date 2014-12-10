# ReadNFC
# Thomas Baer

import nxppy
import httplib

# Domain mit Ziel
server = 'sldev.fritz.box'

# Empfänger an den die gelesene UID angehängt wird
uri = '/info.php?uid='

mifare = nxppy.Mifare()
uid1 = ''
uid2 = ''
while True:
  try:
    uid1 = mifare.select()
    if (uid1 != uid2):
      if (uid1 is not None):
         conn = httplib.HTTPConnection(server)
         conn.request("GET", uri + uid1)
         conn.close()
      uid2 = uid1           
  except nxppy.SelectError:
    pass