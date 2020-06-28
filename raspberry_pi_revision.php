<?php

//-------------------------------------------------------------------------
//
// The MIT License (MIT)
//
// Copyright (c) 2020 Emanuele Barsanti
// Copyright (c) 2015 Andrew Duncan
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to
// permit persons to whom the Software is furnished to do so, subject to
// the following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
// IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
// CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
// TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
// SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//-------------------------------------------------------------------------
//
// The file /proc/cpuinfo contains a line such as:-
//
// Revision    : 0003
//
// that holds the revision number of the Raspberry Pi.
// Known revisions (prior to the Raspberry Pi 2) are:
//
//     +----------+---------+---------+--------+--------------+
//     | Revision |  Model  | PCB Rev | Memory | Manufacturer |
//     +----------+---------+---------+--------+--------------+
//     |   0000   |         |         |        |              |
//     |   0001   |         |         |        |              |
//     |   0002   |    B    |    1    | 256 MB |   Egoman     |
//     |   0003   |    B    |    1    | 256 MB |   Egoman     |
//     |   0004   |    B    |    2    | 256 MB |   Sony UK    |
//     |   0005   |    B    |    2    | 256 MB |   Qisda      |
//     |   0006   |    B    |    2    | 256 MB |   Egoman     |
//     |   0007   |    A    |    2    | 256 MB |   Egoman     |
//     |   0008   |    A    |    2    | 256 MB |   Sony UK    |
//     |   0009   |    A    |    2    | 256 MB |   Qisda      |
//     |   000a   |         |         |        |              |
//     |   000b   |         |         |        |              |
//     |   000c   |         |         |        |              |
//     |   000d   |    B    |    2    | 512 MB |   Egoman     |
//     |   000e   |    B    |    2    | 512 MB |   Sony UK    |
//     |   000f   |    B    |    2    | 512 MB |   Egoman     |
//     |   0010   |    B+   |    1    | 512 MB |   Sony UK    |
//     |   0011   | compute |    1    | 512 MB |   Sony UK    |
//     |   0012   |    A+   |    1    | 256 MB |   Sony UK    |
//     |   0013   |    B+   |    1    | 512 MB |   Embest     |
//     |   0014   | compute |    1    | 512 MB |   Embest     |
//     |   0015   |    A+   |    1    | 256 MB |   Embest     |
//     |   0015   |    A+   |    1    | 512 MB |   Embest     |
//     +----------+---------+---------+--------+--------------+
//
// If the Raspberry Pi has been over-volted (voiding the warranty) the
// revision number will have 100 at the front. e.g. 1000002.
//
//-------------------------------------------------------------------------
//
// With the release of the Raspberry Pi 2, there is a new encoding of the
// Revision field in /proc/cpuinfo. The bit fields are as follows
//
//     +----+----+----+----+----+----+----+----+
//     |FEDC|BA98|7654|3210|FEDC|BA98|7654|3210|
//     +----+----+----+----+----+----+----+----+
//     |    |    |    |    |    |    |    |AAAA|
//     |    |    |    |    |    |BBBB|BBBB|    |
//     |    |    |    |    |CCCC|    |    |    |
//     |    |    |    |DDDD|    |    |    |    |
//     |    |    | EEE|    |    |    |    |    |
//     |    |    |F   |    |    |    |    |    |
//     |    |   G|    |    |    |    |    |    |
//     |    |  H |    |    |    |    |    |    |
//     +----+----+----+----+----+----+----+----+
//     |1098|7654|3210|9876|5432|1098|7654|3210|
//     +----+----+----+----+----+----+----+----+
//
// +---+-------+--------------+--------------------------------------------+
// | # | bits  |   contains   | values                                     |
// +---+-------+--------------+--------------------------------------------+
// | A | 00-03 | PCB Revision | (the pcb revision number)                  |
// | B | 04-11 | Model name   | A, B, A+, B+, B Pi2, Alpha, Compute Module |
// |   |       |              | unknown, B Pi3, Zero, Compute Module 3     |
// |   |       |              | unknown, Zero W, B Pi3+, A Pi3+, unknown,  |
// |   |       |              | Compute Module 3+, B Pi4                   |
// | C | 12-15 | Processor    | BCM2835, BCM2836, BCM2837, BCM2711         |
// | D | 16-19 | Manufacturer | Sony, Egoman, Embest, Sony Japan, Embest,  |
// |   |       |              | Stadium                                    |
// | E | 20-22 | Memory size  | 256 MB, 512 MB, 1024 MB, 2048 MB, 4096 MB, |
// |   |       |              | 8192 MB                                    |
// | F | 23-23 | encoded flag | (if set, revision is a bit field)          |
// | G | 24-24 | waranty bit  | (if set, warranty void - Pre Pi2)          |
// | H | 25-25 | waranty bit  | (if set, warranty void - Post Pi2)         |
// +---+-------+--------------+--------------------------------------------+
//
// Also, due to some early issues the warranty bit has been move from bit
// 24 to bit 25 of the revision number (i.e. 0x2000000). It is also possible
// that both bits may be set (i.e. 0x3000000).
//
// e.g.
//
// Revision    : A01041
//
// A - PCB Revision - 1 (first revision)
// B - Model Name - 4 (Model B Pi 2)
// C - Processor - 1 (BCM2836)
// D - Manufacturer - 0 (Sony)
// E - Memory - 2 (1024 MB)
// F - Endcoded flag - 1 (encoded cpu info)
//
// Revision    : A21041
//
// A - PCB Revision - 1 (first revision)
// B - Model Name - 4 (Model B Pi 2)
// C - Processor - 1 (BCM2836)
// D - Manufacturer - 2 (Embest)
// E - Memory - 2 (1024 MB)
// F - Endcoded flag - 1 (encoded cpu info)
//
// Revision    : 900092
//
// A - PCB Revision - 2 (second revision)
// B - Model Name - 9 (Model Zero)
// C - Processor - 0 (BCM2835)
// D - Manufacturer - 0 (Sony)
// E - Memory - 1 (512 MB)
// F - Endcoded flag - 1 (encoded cpu info)
//
// Revision    : A02082
//
// A - PCB Revision - 2 (first revision)
// B - Model Name - 8 (Model B Pi 3)
// C - Processor - 2 (BCM2837)
// D - Manufacturer - 0 (Sony)
// E - Memory - 2 (1024 MB)
// F - Endcoded flag - 1 (encoded cpu info)
//
// Revision    : A52082
//
// A - PCB Revision - 2 (second revision)
// B - Model Name - 8 (Model B Pi 3)
// C - Processor - 2 (BCM2837)
// D - Manufacturer - 5 (Stadium)
// E - Memory - 2 (1024 MB)
// F - Endcoded flag - 1 (encoded cpu info)
//
// Revision    : 03A01041
//
// A - PCB Revision - 1 (second revision)
// B - Model Name - 4 (Model B Pi 2)
// C - Processor - 1 (BCM2836)
// D - Manufacturer - 0 (Sony UK)
// E - Memory - 2 (1024 MB)
// F - Endcoded flag - 1 (encoded cpu info)
// G - Pre-Pi2 Warranty - 1 (void)
// H - Post-Pi2 Warranty - 1 (void)
//
// Revision    : B03111
//
// A - PCB Revision - 1 (first revision)
// B - Model Name - 17 (Model B Pi 4)
// C - Processor - 3 (BCM2711)
// D - Manufacturer - 0 (Sony UK)
// E - Memory - 32 (2048 MB)
// F - Endcoded flag - 1 (encoded cpu info)

//-------------------------------------------------------------------------

$revisionToMemory = array(
    "RPI_MEMORY_UNKNOWN", //  0
    "RPI_MEMORY_UNKNOWN", //  1
    "RPI_256MB",          //  2
    "RPI_256MB",          //  3
    "RPI_256MB",          //  4
    "RPI_256MB",          //  5
    "RPI_256MB",          //  6
    "RPI_256MB",          //  7
    "RPI_256MB",          //  8
    "RPI_256MB",          //  9
    "RPI_MEMORY_UNKNOWN", //  A
    "RPI_MEMORY_UNKNOWN", //  B
    "RPI_MEMORY_UNKNOWN", //  C
    "RPI_512MB",          //  D
    "RPI_512MB",          //  E
    "RPI_512MB",          //  F
    "RPI_512MB",          // 10
    "RPI_512MB",          // 11
    "RPI_256MB",          // 12
    "RPI_512MB",          // 13
    "RPI_512MB",          // 14
    "RPI_512MB",          // 15
);

$bitFieldToMemory = array(
    "RPI_256MB",  // 0
    "RPI_512MB",  // 1
    "RPI_1024MB", // 2
    "RPI_2048MB", // 3
    "RPI_4096MB", // 4
    "RPI_8192MB", // 5
);

//-------------------------------------------------------------------------

$bitFieldToProcessor = array(
    "RPI_BROADCOM_2835", // 0
    "RPI_BROADCOM_2836", // 1
    "RPI_BROADCOM_2837", // 2
    "RPI_BROADCOM_2711", // 3
);

//-------------------------------------------------------------------------

$revisionToI2CDevice = array(
    "RPI_I2C_DEVICE_UNKNOWN", //  0
    "RPI_I2C_DEVICE_UNKNOWN", //  1
    "RPI_I2C_0",              //  2
    "RPI_I2C_0",              //  3
    "RPI_I2C_1",              //  4
    "RPI_I2C_1",              //  5
    "RPI_I2C_1",              //  6
    "RPI_I2C_1",              //  7
    "RPI_I2C_1",              //  8
    "RPI_I2C_1",              //  9
    "RPI_I2C_DEVICE_UNKNOWN", //  A
    "RPI_I2C_DEVICE_UNKNOWN", //  B
    "RPI_I2C_DEVICE_UNKNOWN", //  C
    "RPI_I2C_1",              //  D
    "RPI_I2C_1",              //  E
    "RPI_I2C_1",              //  F
    "RPI_I2C_1",              // 10
    "RPI_I2C_1",              // 11
    "RPI_I2C_1",              // 12
    "RPI_I2C_1",              // 13
    "RPI_I2C_1",              // 14
    "RPI_I2C_1",              // 15
);

//-------------------------------------------------------------------------

$revisionToModel = array(
    "RPI_MODEL_UNKNOWN",  //  0
    "RPI_MODEL_UNKNOWN",  //  1
    "RPI_MODEL_B",        //  2
    "RPI_MODEL_B",        //  3
    "RPI_MODEL_B",        //  4
    "RPI_MODEL_B",        //  5
    "RPI_MODEL_B",        //  6
    "RPI_MODEL_A",        //  7
    "RPI_MODEL_A",        //  8
    "RPI_MODEL_A",        //  9
    "RPI_MODEL_UNKNOWN",  //  A
    "RPI_MODEL_UNKNOWN",  //  B
    "RPI_MODEL_UNKNOWN",  //  C
    "RPI_MODEL_B",        //  D
    "RPI_MODEL_B",        //  E
    "RPI_MODEL_B",        //  F
    "RPI_MODEL_B_PLUS",   // 10
    "RPI_COMPUTE_MODULE", // 11
    "RPI_MODEL_A_PLUS",   // 12
    "RPI_MODEL_B_PLUS",   // 13
    "RPI_COMPUTE_MODULE", // 14
    "RPI_MODEL_A_PLUS",   // 15
);

$bitFieldToModel = array(
    "RPI_MODEL_A",               //  0
    "RPI_MODEL_B",               //  1
    "RPI_MODEL_A_PLUS",          //  2
    "RPI_MODEL_B_PLUS",          //  3
    "RPI_MODEL_B_PI_2",          //  4
    "RPI_MODEL_ALPHA",           //  5
    "RPI_COMPUTE_MODULE",        //  6
    "RPI_MODEL_UNKNOWN",         //  7
    "RPI_MODEL_B_PI_3",          //  8
    "RPI_MODEL_ZERO",            //  9
    "RPI_COMPUTE_MODULE_3",      //  A
    "RPI_MODEL_UNKNOWN",         //  B
    "RPI_MODEL_ZERO_W",          //  C
    "RPI_MODEL_B_PI_3_PLUS",     //  D
    "RPI_MODEL_A_PI_3_PLUS",     //  E
    "RPI_MODEL_UNKNOWN",         //  F
    "RPI_COMPUTE_MODULE_3_PLUS", // 10
    "RPI_MODEL_B_PI_4",          // 11
);

//-------------------------------------------------------------------------

$bitFieldToManufacturer = array(
    "RPI_MANUFACTURER_SONY_UK",    // 0
    "RPI_MANUFACTURER_EGOMAN",     // 1
    "RPI_MANUFACTURER_EMBEST",     // 2
    "RPI_MANUFACTURER_SONY_JAPAN", // 3
    "RPI_MANUFACTURER_EMBEST",     // 4
    "RPI_MANUFACTURER_STADIUM",    // 5
);

$revisionToManufacturer = array(
    "RPI_MANUFACTURER_UNKNOWN", //  0
    "RPI_MANUFACTURER_UNKNOWN", //  1
    "RPI_MANUFACTURER_EGOMAN",  //  2
    "RPI_MANUFACTURER_EGOMAN",  //  3
    "RPI_MANUFACTURER_SONY_UK", //  4
    "RPI_MANUFACTURER_QISDA",   //  5
    "RPI_MANUFACTURER_EGOMAN",  //  6
    "RPI_MANUFACTURER_EGOMAN",  //  7
    "RPI_MANUFACTURER_SONY_UK", //  8
    "RPI_MANUFACTURER_QISDA",   //  9
    "RPI_MANUFACTURER_UNKNOWN", //  A
    "RPI_MANUFACTURER_UNKNOWN", //  B
    "RPI_MANUFACTURER_UNKNOWN", //  C
    "RPI_MANUFACTURER_EGOMAN",  //  D
    "RPI_MANUFACTURER_SONY_UK", //  E
    "RPI_MANUFACTURER_EGOMAN",  //  F
    "RPI_MANUFACTURER_SONY_UK", // 10
    "RPI_MANUFACTURER_SONY_UK", // 11
    "RPI_MANUFACTURER_SONY_UK", // 12
    "RPI_MANUFACTURER_EMBEST",  // 13
    "RPI_MANUFACTURER_EMBEST",  // 14
    "RPI_MANUFACTURER_EMBEST",  // 15
);

//-------------------------------------------------------------------------

$revisionToPcbRevision = array(
    0, //  0
    0, //  1
    1, //  2
    1, //  3
    2, //  4
    2, //  5
    2, //  6
    2, //  7
    2, //  8
    2, //  9
    0, //  A
    0, //  B
    0, //  C
    2, //  D
    2, //  E
    2, //  F
    1, // 10
    1, // 11
    1, // 12
    1, // 13
    1, // 14
    1  // 15
);

//-------------------------------------------------------------------------

$processorToPeripheralBase = array(
    "RPI_BROADCOM_2835" => "0x20000000",
    "RPI_BROADCOM_2836" => "0x3F000000",
    "RPI_BROADCOM_2837" => "0x3F000000",
    "RPI_BROADCOM_2711" => "0xFE000000",
);

//-------------------------------------------------------------------------


function getRaspberryPiRevision()
{
    if(!@($cpuinfo = file_get_contents("/proc/cpuinfo")))
    {
        return NULL;
    }

    preg_match("/Revision\s+:(.*)/",$cpuinfo,$revision);

    return is_null($revision[1]) ? NULL : trim($revision[1]);
}

$result = 0;

if($_GET["revision"])
{
    $revision = $_GET["revision"];
    if(!ctype_xdigit($revision))
    {
        $result = 3;
    }
}
else
{
    $revision = getRaspberryPiRevision();
}

$info = array(
        "result"            =>  $result,
        "memory"            =>  "RPI_MEMORY_UNKNOWN",
        "processor"         =>  "RPI_PROCESSOR_UNKNOWN",
        "i2cDevice"         =>  "RPI_I2C_DEVICE_UNKNOWN",
        "model"             =>  "RPI_MODEL_UNKNOWN",
        "manufacturer"      =>  "RPI_MANUFACTURER_UNKNOWN",
        "pcbRevision"       =>  0,
        "warrantyVoid"      =>  0,
        "revisionNumber"    =>  $revision,
        "peripheralBase"    =>  "RPI_PERIPHERAL_BASE_UNKNOWN",
    );

if(!$result && !is_null($revision))
{

    // Remove warranty bit
    if(strlen($revision) == 7){
        $revision[-7]=(decbin(base_convert($revision[-7],16,2)) & ~0x3);
    }

    if(base_convert($revision[-6],16,2)[-4])
    {
        // Raspberry Pi2 style revision encoding

        $result = 2;

        $warrantyBit = (decbin(base_convert($info["revisionNumber"][-7],16,2)) & 0x2) == 0x2;

        $memoryBits = base_convert($revision[-6],16,2);
        $memoryBits[-4] = $memoryBits[-4] & 0;

        $memoryIndex = base_convert($memoryBits,2,10);
        if($memoryIndex < count($bitFieldToMemory))
        {
            $memory = $bitFieldToMemory[$memoryIndex];
        }
        else
        {
            $memory = "RPI_MEMORY_UNKNOWN";
        }

        $processorIndex = base_convert($revision[-4],16,10);
        if($processorIndex < count($bitFieldToProcessor))
        {
            $processor = $bitFieldToProcessor[$processorIndex];
        }
        else
        {
            $processor = "RPI_PROCESSOR_UNKNOWN";
        }

        $i2cDevice = "RPI_I2C_1";

        $modelIndex = base_convert($revision[-3].$revision[-2],16,10);
        if($modelIndex < count($revisionToModel))
        {
            $model = $bitFieldToModel[$modelIndex];
        }
        else
        {
            $model = "RPI_MODEL_UNKNOWN";
        }

        $madeByIndex = base_convert($revision[-5],16,10);
        if($madeByIndex < count($bitFieldToManufacturer))
        {
            $manufacturer = $bitFieldToManufacturer[$madeByIndex];
        }
        else
        {
            $manufacturer = "RPI_MANUFACTURER_UNKNOWN";
        }

        $pcbRevision = base_convert($revision[-1],16,10);

        $info["result"]         = $result;
        $info["memory"]         = $memory;
        $info["processor"]      = $processor;
        $info["i2cDevice"]      = $i2cDevice;
        $info["model"]          = $model;
        $info["manufacturer"]   = $manufacturer;
        $info["pcbRevision"]    = $pcbRevision;
        $info["warrantyVoid"]   = (int)$warrantyBit;
        //$info["revisionNumber"] = $revision;
        $info["peripheralBase"] = $processorToPeripheralBase[$processor];
    }
    else
    {
        $result = 1;
        $revision = base_convert($revision,16,10);
        
        $warrantyBit = (decbin(base_convert($info["revisionNumber"][-7],16,2)) & 0x1) == 0x1;
        
        // Original revision encoding
        
        $info["result"]         = $result;
        $info["memory"]         = $revisionToMemory[$revision];
        $info["i2cDevice"]      = $revisionToI2CDevice[$revision];
        $info["model"]          = $revisionToModel[$revision];
        $info["manufacturer"]   = $revisionToManufacturer[$revision];
        $info["pcbRevision"]    = (int)$revisionToPcbRevision[$revision];
        $info["warrantyVoid"]   = (int)$warrantyBit;

        if($info["model"] == $revisionToModel[0])
        {
            $info["processor"] = "RPI_PROCESSOR_UNKNOWN";
        }
        else
        {
            $info["processor"] = "RPI_BROADCOM_2835";
        }

        $info["peripheralBase"] = $processorToPeripheralBase[$info["processor"]];
    }
}

if(!$_GET["machine"]){

    switch($info["memory"])
    {
        case "RPI_256MB":

            $memory = "256 MB";
            break;

        case "RPI_512MB":

            $memory = "512 MB";
            break;

        case "RPI_1024MB":

            $memory = "1024 MB";
            break;

        case "RPI_2048MB":

            $memory = "2048 MB";
            break;

        case "RPI_4096MB":

            $memory = "4096 MB";
            break;

        case "RPI_8192MB":

            $memory = "8192 MB";
            break;

        default:
            $memory = "Unknown";
            break;
    }

    switch($info["processor"])
    {
        case "RPI_BROADCOM_2835":

            $processor = "Broadcom BCM2835";
            break;

        case "RPI_BROADCOM_2836":

            $processor = "Broadcom BCM2836";
            break;

        case "RPI_BROADCOM_2837":

            $processor = "Broadcom BCM2837";
            break;

        case "RPI_BROADCOM_2711":

            $processor = "Broadcom BCM2711";
            break;

        default:
            $processor = "Unknown";
            break;
    }

    switch($info["i2cDevice"])
    {
        case "RPI_I2C_0":

            $i2cDevice = "/dev/i2c-0";
            break;

        case "RPI_I2C_1":

            $i2cDevice = "/dev/i2c-1";
            break;

        default:
            $i2cDevice = "Unknown";
            break;
    }

    switch($info["model"])
    {
        case "RPI_MODEL_A":

            $model = "Raspberry Pi Model A";
            break;

        case "RPI_MODEL_B":

            $model = "Raspberry Pi Model B";
            break;

        case "RPI_MODEL_A_PLUS":

            $model = "Raspberry Pi Model A Plus";
            break;

        case "RPI_MODEL_B_PLUS":

            $model = "Raspberry Pi Model B Plus";
            break;

        case "RPI_MODEL_B_PI_2":

            $model = "Raspberry Pi 2 Model B";
            break;

        case "RPI_MODEL_ALPHA":

            $model = "Raspberry Pi Alpha";
            break;

        case "RPI_COMPUTE_MODULE":

            $model = "Raspberry Pi Compute Module";
            break;

        case "RPI_MODEL_ZERO":

            $model = "Raspberry Pi Model Zero";
            break;

        case "RPI_MODEL_B_PI_3":

            $model = "Raspberry Pi 3 Model B";
            break;

        case "RPI_COMPUTE_MODULE_3":

            $model = "Raspberry Pi Compute Module 3";
            break;

        case "RPI_MODEL_ZERO_W":

            $model = "Raspberry Pi Model Zero W";
            break;

        case "RPI_MODEL_B_PI_3_PLUS":

            $model = "Raspberry Pi 3 Model B Plus";
            break;

        case "RPI_MODEL_A_PI_3_PLUS":

            $model = "Raspberry Pi 3 Model A Plus";
            break;

        case "RPI_COMPUTE_MODULE_3_PLUS":

            $model = "Raspberry Pi Compute Module 3 Plus";
            break;

        case "RPI_MODEL_B_PI_4":

            $model = "Raspberry Pi 4 Model B";
            break;

        default:
            $model = "Unknown";
            break;
    }

    switch($info["manufacturer"])
    {
        case "RPI_MANUFACTURER_SONY_UK":

            $manufacturer = "Sony UK";
            break;

        case "RPI_MANUFACTURER_EGOMAN":

            $manufacturer = "Egoman";
            break;

        case "RPI_MANUFACTURER_QISDA":

            $manufacturer = "Qisda";
            break;

        case "RPI_MANUFACTURER_EMBEST":

            $manufacturer = "Embest";
            break;

        case "RPI_MANUFACTURER_SONY_JAPAN":

            $manufacturer = "Sony Japan";
            break;

        case "RPI_MANUFACTURER_STADIUM":

            $manufacturer = "Stadium";
            break;

        default:
            $manufacturer = "Unknown";
            break;
    }

    switch(intval($info["result"]))
    {
        case 0:
            $result = "failed to get revision from /proc/cpuinfo";
            break;
        case 1:
            $result = "found classic revision number";
            break;
        case 2:
            $result = "found Pi 2 style revision number";
            break;
        case 3:
            $result = "provided revision is not a valid hex encoded number";
            break;
    }

    $info["result"]         = $result;
    $info["memory"]         = $memory;
    $info["processor"]      = $processor;
    $info["i2cDevice"]      = $i2cDevice;
    $info["model"]          = $model;
    $info["manufacturer"]   = $manufacturer;
    $info["warrantyVoid"]   = (int)$warrantyBit? "yes" : "no";
}

echo json_encode($info);

?>