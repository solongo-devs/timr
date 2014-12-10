import nxppy
import httplib

server = 'sldev.fritz.box'
uri = '/timr/g.php?uid='

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