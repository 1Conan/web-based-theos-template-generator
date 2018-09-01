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
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  $packageInfo = [
    'packageName' => !isset($_POST['packageName']) || trim($_POST['packageName']) === '' ? 'com.yourcompany.tweak' : $_POST['packageName'],
    'projectName' => !isset($_POST['projectName']) || trim($_POST['projectName']) === '' ? 'SampleTweak' : $_POST['projectName'],
    'maintainer' => !isset($_POST['maintainer']) || trim($_POST['maintainer']) === '' ? 'Someone' : $_POST['maintainer'],
    'substrateFilter' => !isset($_POST['substrateFilter']) || trim($_POST['substrateFilter']) === '' ? 'com.apple.springboard' : $_POST['substrateFilter'],
    'terminateApp' => !isset($_POST['terminateApp']) || trim($_POST['terminateApp']) === '' ? 'SpringBoard' : $_POST['terminateApp'],
  ];

  $filter = '{ Filter = { Bundles = ( ' . $packageInfo['substrateFilter'] . ' ); }; }';
  
  $aControl = array(
    'Package: ' . $packageInfo['packageName'],
    'Name: ' . $packageInfo['projectName'],
    'Depends: ' . 'mobilesubstrate',
    'Version: ' . '0.0.1',
    'Architecture: ' . 'iphoneos-arm',
    'Description: ' . 'An awesome MobileSubstrate tweak!',
    'Maintainer: ' . $packageInfo['maintainer'],
    'Author: ' . $packageInfo['maintainer'],
    'Section: ' . 'Tweaks',
  );

  $aMakeFile = array(
    'include $(THEOS)/makefiles/common.mk'. PHP_EOL,
    'TWEAK_NAME =' . str_replace(' ', '_', $packageInfo['projectName']),
    'PROJECT_NAME_FILES = Tweak.xm'. PHP_EOL,
    'include $(THEOS_MAKE_PATH)/tweak.mk' . PHP_EOL,
    'after-install::',
    '  install exec "killall -9 ' . $packageInfo['terminateApp'] . '"'
  );

  $zipFileName = str_replace(' ', '_', $packageInfo['projectName']).'-'.generateRandomString().'.zip';

  $zip = new ZipArchive();
  $zip->open($zipFileName, ZipArchive::CREATE);
  $zip->addFromString($packageInfo['projectName'].'.plist', $filter);
  $zip->addFromString('Tweak.xm', '//Sample Tweak');
  $zip->addFromString('control', implode(PHP_EOL, $aControl));
  $zip->addFromString('Makefile', implode(PHP_EOL, $aMakeFile));
  $zip->close();
  header("Content-type: application/zip"); 
  header("Content-Disposition: attachment; filename=$zipFileName"); 
  header("Pragma: no-cache"); 
  header("Expires: 0"); 
  readfile($zipFileName);
  unlink($zipFileName);