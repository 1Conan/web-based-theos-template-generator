<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    header('Location: index.html');
    die();
  }

  function generateRandomString($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  $filter = "{ Filter = { Bundles = ( \"FILTER_HERE\" ); }; }";

  $control = "Package: PACKAGE_NAME\n";
  $control .= "Name: PROJECT_NAME\n";
  $control .= "Depends: mobilesubstrate\n";
  $control .= "Version: 0.0.1\n";
  $control .= "Architecture: iphoneos-arm\n";
  $control .= "Description: An awesome MobileSubstrate tweak!\n";
  $control .= "Maintainer: MAINTAINER\n";
  $control .= "Author: MAINTAINER\n";
  $control .= "Section: Tweaks\n";

  $makefile = "include $(THEOS)/makefiles/common.mk\n\n";
  $makefile .= "TWEAK_NAME = PROJECT_NAME\n";
  $makefile .= "PROJECT_NAME_FILES = Tweak.xm\n\n";
  $makefile .= "include $(THEOS_MAKE_PATH)/tweak.mk\n\n";
  $makefile .= "after-install::\n";
  $makefile .= "  install exec \"killall -9 TERMINATE_APP\"\n";

  $packageInfo = [
    'packageName' => !isset($_POST['packageName']) || trim($_POST['packageName']) === '' ? 'SampleTweak' : $_POST['packageName'],
    'projectName' => !isset($_POST['projectName']) || trim($_POST['projectName']) === '' ? 'com.yourcompany.tweak' : $_POST['projectName'],
    'maintainer' => !isset($_POST['maintainer']) || trim($_POST['maintainer']) === '' ? 'Someone' : $_POST['maintainer'],
    'substrateFilter' => !isset($_POST['substrateFilter']) || trim($_POST['substrateFilter']) === '' ? 'com.apple.springboard' : $_POST['substrateFilter'],
    'terminateApp' => !isset($_POST['terminateApp']) || trim($_POST['terminateApp']) === '' ? 'SpringBoard' : $_POST['terminateApp'],
  ];

  $filter = str_replace('FILTER_HERE', $packageInfo['substrateFilter'], $filter);

  $control = str_replace('PACKAGE_NAME', $packageInfo['packageName'], $control);
  $control = str_replace('PROJECT_NAME', $packageInfo['projectName'], $control);
  $control = str_replace('MAINTAINER', $packageInfo['maintainer'], $control);

  $makefile = str_replace('PROJECT_NAME', str_replace(' ', '_', $packageInfo['projectName']), $makefile);
  $makefile = str_replace('TERMINATE_APP', $packageInfo['terminateApp'], $makefile);

  $zipFileName = str_replace(' ', '_', $packageInfo['projectName']).'-'.generateRandomString().'.zip';

  $zip = new ZipArchive();
  $zip->open($zipFileName, ZipArchive::CREATE);
  $zip->addFromString($packageInfo['projectName'].'.plist', $filter);
  $zip->addFromString('Tweak.xm', '//Sample Tweak');
  $zip->addFromString('control', $control);
  $zip->addFromString('Makefile', $makefile);
  $zip->close();
  header("Content-type: application/zip"); 
  header("Content-Disposition: attachment; filename=$zipFileName"); 
  header("Pragma: no-cache"); 
  header("Expires: 0"); 
  readfile($zipFileName);
  unlink($zipFileName);
  exit;