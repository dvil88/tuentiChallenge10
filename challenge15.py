from pprint import pprint
import os
import sys
import tarfile
import ntpath
import binascii
import zlib
import math


FILE_CHUNK = 512000000
FILE_CHUNK_CRC = 0x487ae926
# FILE_CHUNK = 4096000000
# FILE_CHUNK = 1

FILE_CHUNK = 128000000
FILE_CHUNK_CRC = 0xe5bcf6e4



def sample():
	_input = []
	with open('sampleInput') as f:
		for line in f:
			_input.append(line.strip())
	result = getResults(_input)
def test():
	_input = []
	with open('testInput') as f:
		for line in f:
			_input.append(line.strip())
	result = getResults(_input)
def submit():
	_input = []
	with open('submitInput') as f:
		for line in f:
			_input.append(line.strip())
	result = getResults(_input)

def getResults(inputFile):

	while len(inputFile) != 0:
		line = inputFile.pop(0)
		animal, mods = line.split(' ')

		modifications = []
		for k in range(int(mods)):
			mod = inputFile.pop(0)

			pos, byte = mod.split(' ')
			modifications.append({'position': int(pos), 'byte': int(byte)})


		processModifications(animal, modifications)



def crc32_combine(crc1, crc2, len2):
	# print('   Combine %s and %s' % (hex(crc1), hex(crc2)))
	"""Explanation algorithm: https://stackoverflow.com/a/23126768/654160
	crc32(crc32(0, seq1, len1), seq2, len2) == crc32_combine(
		crc32(0, seq1, len1), crc32(0, seq2, len2), len2)"""
	# degenerate case (also disallow negative lengths)
	if len2 <= 0:
		return crc1

	# put operator for one zero bit in odd
	# CRC-32 polynomial, 1, 2, 4, 8, ..., 1073741824
	odd = [0xedb88320] + [1 << i for i in range(0, 31)]
	even = [0] * 32

	def matrix_times(matrix, vector):
		number_sum = 0
		matrix_index = 0
		while vector != 0:
			if vector & 1:
				number_sum ^= matrix[matrix_index]
			vector = vector >> 1 & 0x7FFFFFFF
			matrix_index += 1
		return number_sum

	# put operator for two zero bits in even - gf2_matrix_square(even, odd)
	even[:] = [matrix_times(odd, odd[n]) for n in range(0, 32)]

	# put operator for four zero bits in odd
	odd[:] = [matrix_times(even, even[n]) for n in range(0, 32)]

	# apply len2 zeros to crc1 (first square will put the operator for one
	# zero byte, eight zero bits, in even)
	while len2 != 0:
		# apply zeros operator for this bit of len2
		even[:] = [matrix_times(odd, odd[n]) for n in range(0, 32)]
		if len2 & 1:
			crc1 = matrix_times(even, crc1)
		len2 >>= 1

		# if no more bits set, then done
		if len2 == 0:
			break

		# another iteration of the loop with odd and even swapped
		odd[:] = [matrix_times(even, even[n]) for n in range(0, 32)]
		if len2 & 1:
			crc1 = matrix_times(odd, crc1)
		len2 >>= 1

		# if no more bits set, then done
	# return combined crc
	crc1 ^= crc2
	return crc1

def createCRCTable():
	initCrc = zlib.crc32(b'\x00')

	crcTable = {
		0: {'crc': initCrc, 'hex': hex(initCrc), 'len': 1 }
	}

	for i in range(1,64):
		length = pow(2, i-1)

		crc = crc32_combine(crcTable[i-1]['crc'], crcTable[i-1]['crc'], length)
		crcTable[i] = {'crc': crc, 'hex': hex(crc), 'len': length}

	return crcTable


def processModifications(file, modifications):
	crcTable = createCRCTable()

	tar = tarfile.open("animals.tar.gz", "r:gz")
	for member in tar.getmembers():
		if member.isdir():
			continue

		fileName = ntpath.basename(member.name)

		if fileName == file:
			fileSize = member.size


			# calculate gaps
			gaps = []
			for modification in modifications:
				gaps.append(modification['position'])

			gaps.sort()

			blockGaps = []
			startingPoint = 0
			for gap in gaps:
				endPoint = gap

				blockGaps.append({'start': startingPoint, 'end': endPoint, 'len': (endPoint - startingPoint) })
				
				startingPoint = gap + 1

			endPoint = fileSize

			for gap in blockGaps:
				length = gap['len']
				length = bin(length)[2:]

				i = 0
				crc = 0
				bitPos = len(length)
				while bitPos:
					if int(length[i]) == 1:
						crc = crc32_combine(crc, crcTable[bitPos - 1 ]['crc'], crcTable[bitPos]['len'])
					bitPos -= 1
					i += 1

				gap['crc'] = crc
				gap['hex'] = hex(crc)


			crc = 0
			partialSize = 0
			for gap in blockGaps:
				partialSize += gap['len']
				crc = crc32_combine(crc, int(gap['crc']), int(gap['len']))

			# lastGap 
			lastGapSize = fileSize - partialSize

			length = bin(lastGapSize)[2:]
			i = 0
			crc2 = 0
			bitPos = len(length)
			while bitPos:
				if int(length[i]) == 1:
					crc2 = crc32_combine(crc2, crcTable[bitPos - 1 ]['crc'], crcTable[bitPos]['len'])
				bitPos -= 1
				i += 1
			crc = crc32_combine(crc, crc2, lastGapSize)

			print('%s 0: %s' % (fileName, format(crc & 0xffffffff, 'x').rjust(8, '0')))



			modBlock = []
			totalModsDone = 0
			for modification in modifications:
				modBlock.append(modification)
				modBlock.sort(key=lambda x: x['position'])


				crc = 0
				partialSize = 0
				for gap in blockGaps:
					partialSize += gap['len']

					crc = crc32_combine(crc, int(gap['crc']), int(gap['len']))

					for mod in modBlock:
						if mod['position'] == gap['end']:
							b = bytearray()
							b.append(mod['byte'])
							crc = crc32_combine(crc, zlib.crc32(b), 1)



				totalModsDone += 1
				# lastGap 
				lastGapSize = fileSize - partialSize 


				length = bin(lastGapSize)[2:]
				i = 0
				crc2 = 0
				bitPos = len(length)
				while bitPos:
					if int(length[i]) == 1:
						crc2 = crc32_combine(crc2, crcTable[bitPos - 1 ]['crc'], crcTable[bitPos]['len'])
					bitPos -= 1
					i += 1

				crc = crc32_combine(crc, crc2, lastGapSize)

				print('%s %d: %s' % (fileName, totalModsDone, format(crc & 0xffffffff, 'x').rjust(8, '0')))





if __name__ == "__main__":
	if sys.argv[1] == 'sample':
		sample()
	elif sys.argv[1] == 'test':
		test()
	elif sys.argv[1] == 'submit':
		submit()
	elif sys.argv[1] == 'detect':
		print('ERROR')
		exit()
