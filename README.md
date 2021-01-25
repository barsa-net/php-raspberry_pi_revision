# raspberry_pi_revision
PHP code to decode the Revision: field of /proc/cpuinfo on the Raspberry Pi - Ported from [AndrewFromMelbourne/raspberry_pi_revision](https://github.com/AndrewFromMelbourne/raspberry_pi_revision)

This script get the revision number from /proc/cpuinfo (by default) or you can manually pass revision number via $_GET["revision"], the output is in JSON in human-readable format (by default) or you can get a slightly more machine-parseable format via $_GET["machine"]

## Usage Examples
**/raspberry_pi_revision.php**

{"result":"found Pi 2 style revision number","memory":"4096 MB","processor":"Broadcom BCM2711","i2cDevice":"\/dev\/i2c-1","model":"Raspberry Pi 4 Model B","manufacturer":"Sony UK","pcbRevision":"1","warrantyVoid":"no","revisionNumber":"c03111","peripheralBase":"0xFE000000"}

**/raspberry_pi_revision.php?revision=900092**

{"result":"found Pi 2 style revision number","memory":"512 MB","processor":"Broadcom BCM2835","i2cDevice":"\/dev\/i2c-1","model":"Raspberry Pi Model Zero","manufacturer":"Sony UK","pcbRevision":"2","warrantyVoid":"no","revisionNumber":"900092","peripheralBase":"0x20000000"}

**/raspberry_pi_revision.php?revision=900092&machine=1**
{"result":2,"memory":"RPI_512MB","processor":"RPI_BROADCOM_2835","i2cDevice":"RPI_I2C_1","model":"RPI_MODEL_ZERO","manufacturer":"RPI_MANUFACTURER_SONY_UK","pcbRevision":"2","warrantyVoid":0,"revisionNumber":"900092","peripheralBase":"0x20000000"}
