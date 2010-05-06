<?php
 /* productChart class definition */
 class productChart extends pChart
  {
  
     var $Palette = array("0"=>array("R"=>57,"G"=>170,"B"=>222),
                        "1"=>array("R"=>123,"G"=>203,"B"=>99),
                        "2"=>array("R"=>247,"G"=>121,"B"=>74),
                        "3"=>array("R"=>247,"G"=>247,"B"=>57),
                        "4"=>array("R"=>255,"G"=>56,"B"=>57),
                        "5"=>array("R"=>165,"G"=>56,"B"=>214),
                        "6"=>array("R"=>82,"G"=>215,"B"=>239),
                        "7"=>array("R"=>224,"G"=>176,"B"=>46));
  
     /* Load Color Palette from file */
   function loadColorPalette($Data)
    {
      $ColorID = 0;
       foreach ($Data as $color)
        {
           $this->Palette[$ColorID]["R"] = $color[0];
           $this->Palette[$ColorID]["G"] = $color[1];
           $this->Palette[$ColorID]["B"] = $color[2];
           $ColorID++;
        }
    }

   function drawBasicPieGraph(&$Data,&$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$R=255,$G=255,$B=255,$Decimals=0)
    {
     /* Validate the Data and DataDescription array */
     $this->validateDataDescription("drawBasicPieGraph",$DataDescription,FALSE);
     $this->validateData("drawBasicPieGraph",$Data);

     /* Determine pie sum */
     $Series = 0; $PieSum = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       if ( $ColName != $DataDescription["Position"] )
        {
         $Series++;
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            $PieSum = $PieSum + $Data[$Key][$ColName]; $iValues[] = $Data[$Key][$ColName]; $iLabels[] = $Data[$Key][$DataDescription["Position"]];
          }
        }
      }

     /* Validate serie */
     if ( $Series != 1 )
      RaiseFatal("Pie chart can only accept one serie of data.");
      if($PieSum == 0) {$PieSum = 0.1;}
     $SpliceRatio         = 360 / $PieSum;
     $SplicePercent       = 100 / $PieSum;

     /* Calculate all polygons */
     $Angle    = 0; $TopPlots = "";
     foreach($iValues as $Key => $Value)
      {
       $TopPlots[$Key][] = $XPos;
       $TopPlots[$Key][] = $YPos;

       /* Process labels position & size */
       if ( !($DrawLabels == PIE_NOLABEL) )
        {
         $TAngle   = $Angle+($Value*$SpliceRatio/2);
         if ($DrawLabels == PIE_PERCENTAGE)
          $Caption  = (floor($Value * pow(10,$Decimals) * $SplicePercent)/pow(10,$Decimals))."%";
         elseif ($DrawLabels == PIE_LABELS)
          $Caption  = $iLabels[$Key];
         $TX       = cos(($TAngle) * 3.1418 / 180 ) * ($Radius + 10)+ $XPos;
         $TY       = sin(($TAngle) * 3.1418 / 180 ) * ($Radius+ 10) + $YPos + 4;

         if ( $TAngle > 90 && $TAngle < 270 )
          {
           $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Caption);
           $TextWidth = $Position[2]-$Position[0];
           $TX = $TX - $TextWidth;
          }

         $C_TextColor = imagecolorallocate($this->Picture,70,70,70);
         imagettftext($this->Picture,$this->FontSize,0,$TX,$TY,$C_TextColor,$this->FontName,$Caption);
        }

       /* Process pie slices */
       for($iAngle=$Angle;$iAngle<=$Angle+$Value*$SpliceRatio;$iAngle=$iAngle+.5)
        {
         $TopX = cos($iAngle * 3.1418 / 180 ) * $Radius + $XPos;
         $TopY = sin($iAngle * 3.1418 / 180 ) * $Radius + $YPos;

         $TopPlots[$Key][] = $TopX; 
         $TopPlots[$Key][] = $TopY;
        }

       $TopPlots[$Key][] = $XPos;
       $TopPlots[$Key][] = $YPos;

       $Angle = $iAngle;
      }
     $PolyPlots = $TopPlots;

     /* Set array values type to float --- PHP Bug with imagefilledpolygon casting to integer */
     foreach ($TopPlots as $Key => $Value)
      { foreach ($TopPlots[$Key] as $Key2 => $Value2) { settype($TopPlots[$Key][$Key2],"float"); } }

     /* Draw Top polygons */
     foreach ($PolyPlots as $Key => $Value)
      { 
       $C_GraphLo = imagecolorallocate($this->Picture,$this->Palette[$Key]["R"],$this->Palette[$Key]["G"],$this->Palette[$Key]["B"]);
       imagefilledpolygon($this->Picture,$PolyPlots[$Key],(count($PolyPlots[$Key])+1)/2,$C_GraphLo);
      }

     $this->drawCircle($XPos-.5,$YPos-.5,$Radius,$R,$G,$B);
     $this->drawCircle($XPos-.5,$YPos-.5,$Radius+.5,$R,$G,$B);

     /* Draw Top polygons */
     foreach ($TopPlots as $Key => $Value)
      { 
       for($j=0;$j<=count($TopPlots[$Key])-4;$j=$j+2)
        $this->drawLine($TopPlots[$Key][$j],$TopPlots[$Key][$j+1],$TopPlots[$Key][$j+2],$TopPlots[$Key][$j+3],$R,$G,$B);
      }
    }
    
   function drawScale(&$Data,&$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1)
    {
     /* Validate the Data and DataDescription array */
     $this->validateData("drawScale",$Data);

     $C_TextColor         = imagecolorallocate($this->Picture,$R,$G,$B);

     $this->drawLine($this->GArea_X1,$this->GArea_Y1,$this->GArea_X1,$this->GArea_Y2,$R,$G,$B);
     $this->drawLine($this->GArea_X1,$this->GArea_Y2,$this->GArea_X2,$this->GArea_Y2,$R,$G,$B);

     if ( $this->VMin == NULL && $this->VMax == NULL)
      {
       if (isset($DataDescription["Values"][0]))
        {
         $this->VMin = $Data[0][$DataDescription["Values"][0]];
         $this->VMax = $Data[0][$DataDescription["Values"][0]];
        }
       else { $this->VMin = 2147483647; $this->VMax = -2147483647; }

       /* Compute Min and Max values */
       if ( $ScaleMode == SCALE_NORMAL || $ScaleMode == SCALE_START0 )
        {
         if ( $ScaleMode == SCALE_START0 ) { $this->VMin = 0; }

         foreach ( $Data as $Key => $Values )
          {
           foreach ( $DataDescription["Values"] as $Key2 => $ColName )
            {
             if (isset($Data[$Key][$ColName]))
              {
               $Value = $Data[$Key][$ColName];

               if ( is_numeric($Value) )
                {
                 if ( $this->VMax < $Value) { $this->VMax = $Value; }
                 if ( $this->VMin > $Value) { $this->VMin = $Value; }
                }
              }
            }
          }
        }
       elseif ( $ScaleMode == SCALE_ADDALL || $ScaleMode == SCALE_ADDALLSTART0 ) /* Experimental */
        {
         if ( $ScaleMode == SCALE_ADDALLSTART0 ) { $this->VMin = 0; }

         foreach ( $Data as $Key => $Values )
          {
           $Sum = 0;
           foreach ( $DataDescription["Values"] as $Key2 => $ColName )
            {
             if (isset($Data[$Key][$ColName]))
              {
               $Value = $Data[$Key][$ColName];
               if ( is_numeric($Value) )
                $Sum  += $Value;
              }
            }
           if ( $this->VMax < $Sum) { $this->VMax = $Sum; }
           if ( $this->VMin > $Sum) { $this->VMin = $Sum; }
          }
        }

       $DataRange = $this->VMax - $this->VMin;
       if ( $DataRange == 0 ) { $DataRange = .1; }

       /* Compute automatic scaling */
       $ScaleOk = FALSE; $Factor = 1;
       $MinDivHeight = 25; $MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $MinDivHeight;
       if ($MaxDivs > 1)
        {
         while(!$ScaleOk)
          {
           $Scale1 = ( $this->VMax - $this->VMin ) / $Factor;
           $Scale2 = ( $this->VMax - $this->VMin ) / $Factor / 2;
           $Scale4 = ( $this->VMax - $this->VMin ) / $Factor / 4;

           if ( $Scale1 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale1); $Scale = 1;}
           if ( $Scale2 <= $MaxDivs && !$ScaleOk) { $ScaleOk = TRUE; $Divisions = floor($Scale2); $Scale = 2;}
           if (!$ScaleOk)
            {
             if ( $Scale2 > 1 ) { $Factor = $Factor * 10; }
             if ( $Scale2 < 1 ) { $Factor = $Factor / 10; }
            }
          }

         if ( floor($this->VMax / $Scale / $Factor) != $this->VMax / $Scale / $Factor)
          {
           $GridID     = floor ( $this->VMax / $Scale / $Factor) + 1;
           $this->VMax = $GridID * $Scale * $Factor;
           $Divisions++;
          }

         if ( floor($this->VMin / $Scale / $Factor) != $this->VMin / $Scale / $Factor)
          {
           $GridID     = floor( $this->VMin / $Scale / $Factor);
           $this->VMin = $GridID * $Scale * $Factor;
           $Divisions++;
          }
        }
       else /* Can occurs for small graphs */
        $Scale = 1;

       if ( !isset($Divisions) )
        $Divisions = 2;

       if ($Scale == 1 && $Divisions%2 == 1)
        $Divisions--;
      }
     else
      $Divisions = $this->Divisions;

     $this->DivisionCount = $Divisions;

     $DataRange = $this->VMax - $this->VMin;
     if ( $DataRange == 0 || $DataRange == 0.1) { $DataRange = 0.1; }
     if ( $Divisions == 0 ) { $Divisions = 0.1; } 
     
     $this->DivisionHeight = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $Divisions;
     $this->DivisionRatio  = ( $this->GArea_Y2 - $this->GArea_Y1 ) / $DataRange;

     $this->GAreaXOffset  = 0;
     if ( count($Data) > 1 )
      {
       if ( $WithMargin == FALSE )
        $this->DivisionWidth = ( $this->GArea_X2 - $this->GArea_X1 ) / (count($Data)-1);
       else
        {
         $this->DivisionWidth = ( $this->GArea_X2 - $this->GArea_X1 ) / (count($Data));
         $this->GAreaXOffset  = $this->DivisionWidth / 2;
        }
      }
     else
      {
       $this->DivisionWidth = $this->GArea_X2 - $this->GArea_X1;
       $this->GAreaXOffset  = $this->DivisionWidth / 2;
      }

     $this->DataCount = count($Data);

     if ( $DrawTicks == FALSE )
      return(0);

     $YPos = $this->GArea_Y2; $XMin = NULL;
     for($i=1;$i<=$Divisions+1;$i++)
      {
       $this->drawLine($this->GArea_X1,$YPos,$this->GArea_X1-5,$YPos,$R,$G,$B);
       $Value     = $this->VMin + ($i-1) * (( $this->VMax - $this->VMin ) / $Divisions);
       $Value     = floor($Value * pow(10,$Decimals)) / pow(10,$Decimals);
       if ( $DataDescription["Format"]["Y"] == "number" )
        $Value = $Value.$DataDescription["Unit"]["Y"];
       if ( $DataDescription["Format"]["Y"] == "time" )
        $Value = $this->ToTime($Value);        
       if ( $DataDescription["Format"]["Y"] == "date" )
        $Value = $this->ToDate($Value);        
       if ( $DataDescription["Format"]["Y"] == "metric" )
        $Value = $this->ToMetric($Value);        

       $Position  = imageftbbox($this->FontSize,0,$this->FontName,$Value);
       $TextWidth = $Position[2]-$Position[0];
       imagettftext($this->Picture,$this->FontSize,0,$this->GArea_X1-10-$TextWidth,$YPos+($this->FontSize/2),$C_TextColor,$this->FontName,$Value);

       if ( $XMin > $this->GArea_X1-10-$TextWidth || $XMin == NULL ) { $XMin = $this->GArea_X1-10-$TextWidth; }

       $YPos = $YPos - $this->DivisionHeight;
      }

     /* Write the Y Axis caption if set */ 
     if ( isset($DataDescription["Axis"]["Y"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["Y"]);
       $TextHeight = abs($Position[1])+abs($Position[3]);
       $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight/2);
       imagettftext($this->Picture,$this->FontSize,90,$XMin-$this->FontSize,$TextTop,$C_TextColor,$this->FontName,$DataDescription["Axis"]["Y"]);
      }

     /* Horizontal Axis */
     $XPos = $this->GArea_X1 + $this->GAreaXOffset;
     $ID = 1; $YMax = NULL;
     foreach ( $Data as $Key => $Values )
      {
       if ( $ID % $SkipLabels == 0 )
        {
         $this->drawLine(floor($XPos),$this->GArea_Y2,floor($XPos),$this->GArea_Y2+5,$R,$G,$B);
         $Value      = $Data[$Key][$DataDescription["Position"]];
         if ( $DataDescription["Format"]["X"] == "number" )
          $Value = $Value.$DataDescription["Unit"]["X"];
         if ( $DataDescription["Format"]["X"] == "time" )
          $Value = $this->ToTime($Value);        
         if ( $DataDescription["Format"]["X"] == "date" )
          $Value = $this->ToDate($Value);        
         if ( $DataDescription["Format"]["X"] == "metric" )
          $Value = $this->ToMetric($Value);        

         $Position   = imageftbbox($this->FontSize,$Angle,$this->FontName,$Value);
         $TextWidth  = abs($Position[2])+abs($Position[0]);
         $TextHeight = abs($Position[1])+abs($Position[3]);

         if ( $Angle == 0 )
          {
           $YPos = $this->GArea_Y2+18;
           imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-floor($TextWidth/2),$YPos,$C_TextColor,$this->FontName,$Value);
          }
         else
          {
           $YPos = $this->GArea_Y2+10+$TextHeight;
           if ( $Angle <= 90 )
            imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)-$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
           else
            imagettftext($this->Picture,$this->FontSize,$Angle,floor($XPos)+$TextWidth+5,$YPos,$C_TextColor,$this->FontName,$Value);
          }
         if ( $YMax < $YPos || $YMax == NULL ) { $YMax = $YPos; }
        }

       $XPos = $XPos + $this->DivisionWidth;
       $ID++;
      }

    /* Write the X Axis caption if set */ 
    if ( isset($DataDescription["Axis"]["X"]) )
      {
       $Position   = imageftbbox($this->FontSize,90,$this->FontName,$DataDescription["Axis"]["X"]);
       $TextWidth  = abs($Position[2])+abs($Position[0]);
       $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth/2);
       imagettftext($this->Picture,$this->FontSize,0,$TextLeft,$YMax+$this->FontSize+5,$C_TextColor,$this->FontName,$DataDescription["Axis"]["X"]);
      }
    }
    
    function drawLineGraph(&$Data,&$DataDescription,$SerieName="") {
     $this->validateDataDescription("drawLineGraph",$DataDescription);
     $this->validateData("drawLineGraph",$Data);

     $GraphID = 0;
     foreach ( $DataDescription["Values"] as $Key2 => $ColName )
      {
       $ID = 0;
       foreach ( $DataDescription["Description"] as $keyI => $ValueI )
        { if ( $keyI == $ColName ) { $ColorID = $ID; }; $ID++; }

       if ( $SerieName == "" || $SerieName == $ColName )
        {
         $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
         $XLast = -1;
         foreach ( $Data as $Key => $Values )
          {
           if ( isset($Data[$Key][$ColName]))
            {
             $Value = $Data[$Key][$ColName];
             $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

             if (!is_numeric($Value)) { $XLast = -1; }
             if ( $XLast != -1 )
              $this->drawLine($XLast,$YLast,$XPos,$YPos,0,117,206,TRUE);

             $XLast = $XPos;
             $YLast = $YPos;
             if (!is_numeric($Value)) { $XLast = -1; }
            }
           $XPos = $XPos + $this->DivisionWidth;
          }
         $GraphID++;
        }
      }     
    }
  }
?>