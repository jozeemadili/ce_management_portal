<?php
namespace App\TIRAClient\Scripts\Classes;

use Carbon\Carbon;
use Exception;
use PhpParser\Node\Expr\Cast\String_;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{
    public static function saveLogs($fileName, $logs)
    {
    //   $myfile = fopen("_logs_".$fileName.".txt", "w") or die("Unable to open file!");
    //   fwrite($myfile, $logs);
    //   fwrite($myfile, json_encode($logs)); 
    //   fclose($myfile);
    }

    public static function dateDifference($start, $end, $whatToReturn = "m")
    {
        try{
            $toDate      = Carbon::parse($start);
            $fromDate    = Carbon::parse($end);
            $days        = $toDate->diffInDays($fromDate);
            $months      = $toDate->diffInMonths($fromDate);
            $years       = $toDate->diffInYears($fromDate);
            return ($whatToReturn == "m" ? $months : ($whatToReturn == "y" ? $years : $days));
        }
        catch(Exception $e)
        {
            return 12;
        }
    }

    public static function addToDate($date, $qntyToAdd, $whatToAdd = "d")
    {
        try
        {
            $originalDate = Carbon::parse($date);
            if($whatToAdd == "h")
            {
                return $originalDate->addHours($qntyToAdd)->toDateTimeString();
            }
            else 
            {
               return $originalDate->addDays($qntyToAdd)->toDateTimeString();
            }
        }
        catch(Exception $e)
        {
            return $originalDate;
        }
    }
    public static function getQrcode($contents, $size = 80, $format = null)
    {
        if($format != null)
        {
            $qr = QrCode::size($size)->format(strtolower($format))->generate($contents);
            //self::saveLogs("QrGenerated", $qr);
            return $qr;
        }
        else
        {
            $qr = QrCode::size($size)->generate($contents);
            //self::saveLogs("QrGenerated", $qr);
            return $qr;
        }
    }

    public static function svgToBase64 ($get_img)
    {
            return 'data:image/svg'  . ';base64,' . base64_encode($get_img );
    }
    public static function shortTermPremiumRate($numberOfDays)
    {
        if($numberOfDays<=30)
        {
            return 0.2;
        }
        else if($numberOfDays>30 && $numberOfDays<=90)
        {
            return 0.4;
        }
        else if($numberOfDays>90 && $numberOfDays<=180)
        {
            return 0.7;
        }
        else if($numberOfDays>180 && $numberOfDays<=270)
        {
            return 0.85;
        }
        else if($numberOfDays>270 && $numberOfDays<=366)
        {
            return 1;
        }
        else
        {
            return 1;
        }

    }

    public static function getMNOType($mobilePhone)
    {
        $networkCode = substr($mobilePhone, 0 , 2);
        if($networkCode == "65" || $networkCode == "67" || $networkCode == "71")
        {
            return "TigoPesa";
        }
        else if($networkCode == "68" || $networkCode == "78")
        {
            return "AirtelMoney";
        }
        else if($networkCode == "73")
        {
            return "TPesa";
        }
        else if($networkCode == "74" || $networkCode == "75" || $networkCode == "76")
        {
            return "Mpesa";
        }
        else if($networkCode == "77")
        {
            return "EzzyPesa";
        }
        else
        {
            return "Mpesa";
        }
    }

    public static function getJubileeProductNumber($tiraProductCode)
    {
        if($tiraProductCode == "SP014003000000")
        {
            //Motor Commercial
            return "1001";
        }
        else if($tiraProductCode == "SP014001000000")
        {
            //Motor Private
            return "1002";
        }
        else if($tiraProductCode == "SP014002000000")
        {
            //Motor Cycle
            return "1005";
        }
        else if($tiraProductCode == "1006")
        {
            return "1006";
        }
        else if($tiraProductCode == "1007")
        {
            return "1007";
        }
        else
        {
            return "1002";
        }
    }

    public static function getJubileeCoverType($riskName)
    {

        if(self::containsAll(strtolower($riskName), ['comprehensive']))
        {
            return "COMPREHENSIVE";
        }
        else if(self::containsAll(strtolower($riskName), ['third', 'fire']))
        {
            return "THIRD PARTY FIRE AND THEFT";
        }
        else 
        {
            return "THIRD PARTY ONLY";
        }
    }

    public static function getJubileeMotorUsageType($usage)
    {

        if($usage == "Private")
        {
            return "PRIVATE";
        }
        else if($usage == "Commercial")
        {
            return "COMMERCIAL";
        }
        else if($usage == "TRADE PLATE")
        {
            return "TRADE PLATE";
        }
        else if($usage == "SPECIAL TYPE")
        {
            return "SPECIAL TYPE";
        }
        else if($usage == "TWO-WHEELER")
        {
            return "TWO-WHEELER";
        }
        else if($usage == "THREE-WHEELER")
        {
            return "THREE-WHEELER";
        }
        else 
        {
            return "PRIVATE";
        }
    }

    public static function getJubileePaymentMode($payment_method)
    {

        if($payment_method == 1)
        {
            return "CASH";
        }
        else if($payment_method == 2)
        {
            return "BANK";
        }
        else if($payment_method == 3)
        {
            return "MOBILE";
        }
        else 
        {
            return "CASH";
        }
    }

    public static function formatMobileNumber($mobilePhone)
    {
        return "255".$mobilePhone;
    }

    public static function timeGreeting()
    {
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $time = date("H");
        if ($time < "12") {
            return "Good morning";
        } 
        else if ($time >= "12" && $time < "17") {
            return "Good afternoon";
        } 
        else if ($time >= "17" && $time < "19") {
            return "Good evening";
        } else if ($time >= "19") {
            return "Good night";
        }
    }

    public static function numberToWord($num = '')
    {
        $num    = ( string ) ( ( int ) $num );
        
        if( ( int ) ( $num ) && ctype_digit( $num ) )
        {
            $words  = array( );
             
            $num    = str_replace( array( ',' , ' ' ) , '' , trim( $num ) );
             
            $list1  = array('','one','two','three','four','five','six','seven',
                'eight','nine','ten','eleven','twelve','thirteen','fourteen',
                'fifteen','sixteen','seventeen','eighteen','nineteen');
             
            $list2  = array('','ten','twenty','thirty','forty','fifty','sixty',
                'seventy','eighty','ninety','hundred');
             
            $list3  = array('','thousand','million','billion','trillion',
                'quadrillion','quintillion','sextillion','septillion',
                'octillion','nonillion','decillion','undecillion',
                'duodecillion','tredecillion','quattuordecillion',
                'quindecillion','sexdecillion','septendecillion',
                'octodecillion','novemdecillion','vigintillion');
             
            $num_length = strlen( $num );
            $levels = ( int ) ( ( $num_length + 2 ) / 3 );
            $max_length = $levels * 3;
            $num    = substr( '00'.$num , -$max_length );
            $num_levels = str_split( $num , 3 );
             
            foreach( $num_levels as $num_part )
            {
                $levels--;
                $hundreds   = ( int ) ( $num_part / 100 );
                $hundreds   = ( $hundreds ? ' ' . $list1[$hundreds] . ' Hundred' . ( $hundreds == 1 ? '' : 's' ) . ' ' : '' );
                $tens       = ( int ) ( $num_part % 100 );
                $singles    = '';
                 
                if( $tens < 20 ) { $tens = ( $tens ? ' ' . $list1[$tens] . ' ' : '' ); } else { $tens = ( int ) ( $tens / 10 ); $tens = ' ' . $list2[$tens] . ' '; $singles = ( int ) ( $num_part % 10 ); $singles = ' ' . $list1[$singles] . ' '; } $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_part ) ) ? ' ' . $list3[$levels] . ' ' : '' ); } $commas = count( $words ); if( $commas > 1 )
            {
                $commas = $commas - 1;
            }
             
            $words  = implode( ', ' , $words );
             
            $words  = trim( str_replace( ' ,' , ',' , ucwords( $words ) )  , ', ' );
            if( $commas )
            {
                $words  = str_replace( ',' , ' and' , $words );
            }
             
            return $words;
        }
        else if( ! ( ( int ) $num ) )
        {
            return 'Zero';
        }
        return '';
    }


    public static function searchInArray($array, $key, $value) 
    {
        foreach ($array as $item) 
        {
            if ($item[$key] == $value) 
            {
                return $item;
            }
        }
        return null;
    }

    public static function containsAll(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) === false) {
                return false;
            }
        }
        return true;
    }
    

}
