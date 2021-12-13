<?php
require 'env.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require 'vendor/autoload.php';

$pattern_array = ["docx", "doc"];
$file = 'CAP VILLAS DEVELOPMENT BRIEF v1.docx';
unlink("doclist.txt");
rsearch($folder, $pattern_array);

function getNumberOfPages($filename)
{
    $pages = false;
    $pages = get_num_pages_doc1($filename);
    if (!$pages) {
        $pages = get_num_pages_doc2($filename);
    }
    if (!$pages) {
        $pages = get_num_pages_doc3($filename);
    }
    if (!$pages) {
        $pages = get_num_pages_doc4($filename);
    }

//    if (!$pages) {
//        $pages = -1;
//        $hexDocInfo = get_num_pages_doc($filename);
//        $docInfo = hex_ascii($hexDocInfo);
//        //var_dump($docInfo);
//    }

    echo "Pages: " . $pages . "\n";
}

function rsearch($folder, $pattern_array)
{
    $return = array();
    $iti = new RecursiveDirectoryIterator($folder);
    foreach (new RecursiveIteratorIterator($iti) as $file) {
        if (in_array(strtolower(array_pop(explode('.', $file))), $pattern_array)) {
            $pageNo = getNumberOfPages($file);
            $line = join(",", [$pageNo, $file]);
            file_put_contents("doclist.txt", $line . "\n", FILE_APPEND);
            //$return[] = '"' . $file ."\n";
        }
    }
    return $return;
}

function getDocPageNo($filename)
{
    $extension = "***";
    switch ($extension) {
        case 'docx':
            shell_exec("unzip -p 'sample.docx' docProps/app.xml | grep -oP '(?<=\<Pages\>).*(?=\</Pages\>)'");
            break;
        case 'pptx':
            shell_exec("unzip -p 'sample.pptx' docProps/app.xml | grep -oP '(?<=\<Slides\>).*(?=\</Slides\>)'");
        case 'doc':
            shell_exec("wvSummary sample.doc | grep -oP '(?<=of Pages = )[ A-Za-z0-9]*'");
        case'ppt':
            shell_exec("wvSummary sample.ppt | grep -oP '(?<=of Slides = )[ A-Za-z0-9]*'");
        case 'odt':
            shell_exec("unzip -p sample.odt meta.xml | grep -oP '(?<=page-count=\")[ A-Za-z0-9]*'");
        case 'pdf':
            shell_exec("pdfinfo sample.pdf | grep -oP '(?<=Pages:          )[ A-Za-z0-9]*'");
        case 'djv':
            shell_exec('djvused -e "n" sample.djvu');
    }
}

/**
 *
 * @param type $filename
 * @return boolean
 */
function get_num_pages_doc1($filename)
{
    $zip = new ZipArchive();
    if ($zip->open($filename) === true) {
        if (($index = $zip->locateName('docProps/app.xml')) !== false) {
            $data = $zip->getFromIndex($index);
            $zip->close();

            $xml = new SimpleXMLElement($data);
            return $xml->Pages;
        }
        $zip->close();
    }
    return false;
}

function get_num_pages_doc2($filename)
{
    $pages = shell_exec("wvSummary '${filename}' | grep -oP '(?<=of Pages = )[ A-Za-z0-9]*'");
    return $pages;
}

function get_num_pages_doc3($filename)
{
    return false;
}

function get_num_pages_doc4($filename)
{
    $handle = fopen($filename, 'r');
    $line = @fread($handle, filesize($filename));

    //echo '<div style="font-family: courier new;">';

    $hex = bin2hex($line);
    $hex_array = str_split($hex, 4);
    $i = 0;
    $line = 0;
    $collection = '';
    foreach ($hex_array as $key => $string) {
        $collection .= hex_ascii($string);
        $i++;

        if ($i == 1) {
            //echo '<b>' . sprintf('%05X', $line) . '0:</b> ';
        }

        //echo strtoupper($string) . ' ';

        if ($i == 8) {
            echo ' ' . $collection . ' <br />' . "\n";
            $collection = '';
            $i = 0;

            $line += 1;
        }
    }

    //echo '</div>';
    //exit();
    return null;
}

function hex_ascii($string, $html_safe = true)
{
    $return = '';

    $conv = array($string);
    if (strlen($string) > 2) {
        $conv = str_split($string, 2);
    }

    foreach ($conv as $string) {
        $num = hexdec($string);

        $ascii = '.';
        if ($num > 32) {
            $ascii = unichr($num);
        }

        if ($html_safe AND ($num == 62 OR $num == 60)) {
            $return .= htmlentities($ascii);
        } else {
            $return .= $ascii;
        }
    }

    return $return;
}

function unichr($intval)
{
    return mb_convert_encoding(pack('n', $intval), 'UTF-8', 'UTF-16BE');
}
