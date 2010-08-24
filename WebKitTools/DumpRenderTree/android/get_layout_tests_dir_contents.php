<?php
# Copyright (C) 2010 The Android Open Source Project
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

###############################################################################

# Lists the content of the LayoutTests directory
#
# Usage:
#   get_layout_tests_dir_contents.php?path=PATH&recurse=RECURSE&separator=SEPARATOR&mode=MODE
#   where
#     PATH - relative path in the LayoutTests dir
#     RECURSE = [true|false] (defaults to true)
#     SEPARATOR = a string separating paths in result (defaults to \n)
#     MODE = [folders|files] (defaults to files) - if 'folders' then lists only folders,
#                                                  if 'files' then only files

  #Global variables
  $rootDir =
    dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR .
    basename($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR .
    "LayoutTests";

  function getAbsolutePath($relPath) {
    global $rootDir;
    return $rootDir . DIRECTORY_SEPARATOR . $relPath;
  }

  function getFilesAsArray($relPath) {
    return array_slice(scandir(getAbsolutePath($relPath)), 2);
  }

  function isIgnored($basename) {
    return substr($basename, 0, 1) == '.';
  }

  function getAllFilesUnderAsArray($relPath, $recurse, $mode) {
    global $exclude;
    global $rootDir;
    $files = getFilesAsArray($relPath);
    $result = array();

    foreach($files as $i => $value) {
      if (isIgnored($value)) {
        continue;
      }
      if ($relPath == '') {
        $filePath = $value;
      } else {
        $filePath = $relPath . DIRECTORY_SEPARATOR . $value;
      }

      if (is_dir(getAbsolutePath($filePath))) {
        if ($mode == 'folders') {
          $result = array_merge($result, (array)$filePath);
        }
        if ($recurse) {
          $result = array_merge($result, getAllFilesUnderAsArray($filePath, $recurse, $mode));
        }
      } else if ($mode == 'files') {
        $result = array_merge($result, (array)$filePath);
      }
    }

    return $result;
  }

  function main() {
    global $rootDir;

    $path = getAbsolutePath($_GET['path']);

    if (!isset($_GET['separator'])) {
      $separator = "\n";
    } else {
      $separator = $_GET['separator'];
    }

    $recurse = (strtolower($_GET['recurse']) != 'false');

    if (strtolower($_GET['mode']) == 'folders') {
      $mode = 'folders';
    } else {
      $mode = 'files';
    }

    #Very primitive check if path tries to go above DOCUMENT_ROOT or is absolute
    if (strpos($_GET['path'], "..") !== False ||
        substr($_GET['path'], 0, 1) == DIRECTORY_SEPARATOR) {
      return;
    }

    #If we don't want realpath to append any prefixes we need to pass it an absolute path
    $path = realpath(getAbsolutePath($_GET['path']));
    $relPath = substr($path, strlen($rootDir) + 1);

    #If there is an error of some sort it will be output as a part of the answer!
    foreach (getAllFilesUnderAsArray($relPath, $recurse, $mode) as $i => $value) {
      echo "$value$separator";
    }
  }

  main();
?>
