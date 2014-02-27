
import argparse
import gzip
import bz2


class CompressedFileType(argparse.FileType):
    def __call__(self, string):
        try:
            if string.endswith('.gz'):
                return gzip.open(string, self._mode, self._bufsize)
            if string.endswith('.bz2'):
                return bz2.BZ2File(string, self._mode, self._bufsize)
        except IOError as e:
            message = "can't open '%s': %s"
            raise argparse.ArgumentTypeError(message % (string, e))
        return super(CompressedFileType, self).__call__(string)