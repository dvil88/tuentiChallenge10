from gmpy2 import gcd
import binascii



m1_file = 'plaintexts/test1.txt'
with open(m1_file, 'rb') as f:
    content = f.read()
m1 = int(binascii.hexlify(content), 16)


m2_file = 'plaintexts/test2.txt'
with open(m2_file, 'rb') as f:
    content = f.read()
m2 = int(binascii.hexlify(content), 16)


c1_file = 'ciphered/test1.txt'
with open(c1_file, 'rb') as f:
    content = f.read()
c1 = int(binascii.hexlify(content), 16)


c2_file = 'ciphered/test2.txt'
with open(c2_file, 'rb') as f:
    content = f.read()
c2 = int(binascii.hexlify(content), 16)



e = 65537
mod = gcd( pow(m1, e) - c1, pow(m2, e) - c2 )
print('Key: %d' % mod)

testFile = open('testResult.txt', 'w')
testFile.write("%d" % mod)
testFile.close()

submitFile = open('submitResult.txt', 'w')
submitFile.write("%d" % mod)
submitFile.close()