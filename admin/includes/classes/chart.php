<?php
/*
  $Id: chart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include('external/pChart/pChart/pData.php');
  include('external/pChart/pChart/pChart.php');
  include('external/pChart/pChart/productChart.php');
  include('external/pChart/pChart/configure.php'); 
  
  class osC_Chart {
    var $_width = 700,
        $_height = 230,
        $_font = 8;
    
    function CreatePlotImage($filename, $data, $n) {

      $serie1 = array();
      $serie2 = array();

      if(sizeof($data) > 0) {
        foreach($data as $date => $visits ) {
          $serie1[] = $visits;
          $serie2[] = strtotime($date);
        }
      } else {
        for( $i = 0; $i < $n; $i++ ) {
          $serie1[] = 0;
        }
      }
      
      $DataSet = new pData;
      $DataSet->AddPoint($serie1, "Serie1");
      $DataSet->AddSerie("Serie1");
      $DataSet->SetAbsciseLabelSerie("Serie2");
      $DataSet->SetSerieName(" ","Serie1");

      $chart = new productChart($this->_width, $this->_height);
      $chart->setFontProperties("external/pChart/Fonts/tahoma.ttf", $this->_font);
      $chart->setGraphArea(PLOT_SETGRAPHAREA_X1, PLOT_SETGRAPHAREA_Y1, PLOT_SETGRAPHAREA_X2, PLOT_SETGRAPHAREA_Y2);
      $chart->drawFilledRoundedRectangle(PLOT_FILLEDROUNDEDRECTANGLE_X1, PLOT_FILLEDROUNDEDRECTANGLE_Y1, PLOT_FILLEDROUNDEDRECTANGLE_X2, PLOT_FILLEDROUNDEDRECTANGLE_Y2, PLOT_FILLEDROUNDEDRECTANGLE_RADIUS, PLOT_FILLEDROUNDEDRECTANGLE_R, PLOT_FILLEDROUNDEDRECTANGLE_G, PLOT_FILLEDROUNDEDRECTANGLE_B);
      $chart->drawRoundedRectangle(PLOT_ROUNDEDRECTANGLE_X1, PLOT_ROUNDEDRECTANGLE_Y1, PLOT_ROUNDEDRECTANGLE_X2, PLOT_ROUNDEDRECTANGLE_Y2, PLOT_ROUNDEDRECTANGLE_RADIUS, PLOT_ROUNDEDRECTANGLE_R, PLOT_ROUNDEDRECTANGLE_G, PLOT_ROUNDEDRECTANGLE_B);
      $chart->drawGraphArea(PLOT_DRAWGRAPHAREA_R, PLOT_DRAWGRAPHAREA_G, PLOT_DRAWGRAPHAREA_B, TRUE);
      $chart->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_NORMAL, PLOT_DRAWSCALE_R, PLOT_DRAWSCALE_G, PLOT_DRAWSCALE_B, TRUE, PLOT_DRAWSCALE_ANGLE, PLOT_DRAWSCALE_DECIMALS);
      $chart->drawGrid(PLOT_DRAWGRID_LINEWIDTH, TRUE, PLOT_DRAWGRID_R, PLOT_DRAWGRID_G, PLOT_DRAWGRID_B, PLOT_DRAWGRID_ALPHA);
      
      $chart->setFontProperties("external/pChart/Fonts/tahoma.ttf",$this->_font);
      $chart->drawTreshold(PLOT_DRAWTRESHOLD_VALUE, PLOT_DRAWTRESHOLD_R, PLOT_DRAWTRESHOLD_G, PLOT_DRAWTRESHOLD_B, TRUE, TRUE);
      
      $chart->drawArea($DataSet->GetData(), "Serie1", "Serie2", PLOT_DRAWAREA_R, PLOT_DRAWAREA_G, PLOT_DRAWAREA_B, PLOT_DRAWAREA_ALPHA);
      
      $chart->drawLineGraph($DataSet->GetData(), $DataSet->GetDataDescription());
      $chart->drawPlotGraph($DataSet->GetData(), $DataSet->GetDataDescription(), PLOT_DRAWPLOTGRAPH_BIGRADIUS, PLOT_DRAWPLOTGRAPH_SMALLRADIUS, PLOT_DRAWPLOTGRAPH_R, PLOT_DRAWPLOTGRAPH_G, PLOT_DRAWPLOTGRAPH_B);
      
      $chart->setFontProperties("external/pChart/Fonts/tahoma.ttf", $this->_font);
      $chart->drawLegend(PLOT_DRAWLEGEND_XPOS, PLOT_DRAWLEGEND_YPOS, $DataSet->GetDataDescription(), PLOT_DRAWLEGEND_R, PLOT_DRAWLEGEND_G, PLOT_DRAWLEGEND_B);
      $chart->Render($filename);
      
      return true;
    }
    
    function CreatePieImage($filename, $data) {
    
      $serie1 = array();
      $serie2 = array();
      
      if(sizeof($data) > 0) {
        foreach($data as $contry => $visits ) {
          $serie1[] = $visits;
          $serie2[] = $contry;
        }    
      } else {
        $serie1[] = 0;
        $serie2[] = '';
      }
    
      $DataSet = new pData;
      $DataSet->AddPoint($serie1,"Serie1");
      $DataSet->AddPoint($serie2,"Serie2");
      $DataSet->AddAllSeries();
      $DataSet->SetAbsciseLabelSerie("Serie2");
      
      $chart = new productChart($this->_width, $this->_height);
      $chart->drawFilledRoundedRectangle(PIE_FILLEDROUNDEDRECTANGLE_X1, PIE_FILLEDROUNDEDRECTANGLE_Y1, PIE_FILLEDROUNDEDRECTANGLE_X2, PIE_FILLEDROUNDEDRECTANGLE_Y2, PIE_FILLEDROUNDEDRECTANGLE_RADIUS, PIE_FILLEDROUNDEDRECTANGLE_R, PIE_FILLEDROUNDEDRECTANGLE_G, PIE_FILLEDROUNDEDRECTANGLE_B);
      $chart->drawRoundedRectangle(PIE_ROUNDEDRECTANGLE_X1, PIE_ROUNDEDRECTANGLE_Y1, PIE_ROUNDEDRECTANGLE_X2, PIE_ROUNDEDRECTANGLE_Y2, PIE_ROUNDEDRECTANGLE_RADIUS, PIE_ROUNDEDRECTANGLE_R, PIE_ROUNDEDRECTANGLE_G, PIE_ROUNDEDRECTANGLE_B);
      
      $chart->setFontProperties("external/pChart/Fonts/tahoma.ttf", $this->_font);
      $chart->drawBasicPieGraph($DataSet->GetData(), $DataSet->GetDataDescription(), PIE_DRAWBASICPIEGRAPH_XPOS, PIE_DRAWBASICPIEGRAPH_YPOS, PIE_DRAWBASICPIEGRAPH_RADIUS, PIE_PERCENTAGE, PIE_DRAWBASICPIEGRAPH_R, PIE_DRAWBASICPIEGRAPH_G, PIE_DRAWBASICPIEGRAPH_B);
      $chart->drawPieLegend(PIE_DRAWPIELEGEND_XPOS, PIE_DRAWPIELEGEND_YPOS, $DataSet->GetData(), $DataSet->GetDataDescription(), PIE_DRAWPIELEGEND_R, PIE_DRAWPIELEGEND_G, PIE_DRAWPIELEGEND_B);
      
      $chart->Render($filename);
      
      return true;
    }
  }
?>
