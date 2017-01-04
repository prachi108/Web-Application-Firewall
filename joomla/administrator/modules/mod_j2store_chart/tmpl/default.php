<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
/**
  * Get the document.
  */
$document = JFactory::getDocument();

$document->addScript(JUri::root().'administrator/modules/mod_j2store_chart/media/chart_api.js');
$currency = J2Store::currency();
//chart script.
$script = '
 google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);

         function drawChart() {

        //year chart axis.

        var yearchart = google.visualization.arrayToDataTable([

         ["'.JText::_("J2STORE_YEAR").'","'.JText::_("J2STORE_AMOUNT").'"],

         ';

       /**
		 * array of items.
		 */
        foreach($years as $order) {
			//year charts properties.
			$script .="

			['".$order['dyear']."',".$order['total']."],

			";

		}

    $script .=']);


    //month chart axis.

    var monthchart = google.visualization.arrayToDataTable([

    ["'.JText::_("J2STORE_CHART_MONTH").'","'.JText::_("J2STORE_CHART_TOTAL_AMOUNT").'"],
    ';

    /**
     * array of itemsmonth.
     */
    foreach($months as $item) {

    	//$months = array('','January','Feb','March','April','May','June','July','Auguest','Sep','Oct','Nov','Dec');

    	//month charts properties.
    	$script .='

    	["'.$item['dmonth'].'",'.$item['total'].'],

    	';

    }
    $script .=']);

    //day chart axis.

    var daychart = google.visualization.arrayToDataTable([
    ["'.JText::_("J2STORE_CHART_MONTH").'","'.JText::_("J2STORE_CHART_TOTAL_AMOUNT").'"],
    ';

    /**
     * array of items.
     */
	if(!empty($days)){

		foreach($days as $itemday){
   		//day charts properties.
    	$script .='
    	["'.$itemday['dday'].'",'.$itemday['total'].'],

    	';

    }
}

  //  echo JText::_("J2STORE_CHART_TOTAL_AMOUNT");

	$year_title = JText::_('MOD_J2STORE_CHART_YEARLY_SALES_REPORT');
	$monthly_title = JText::_('MOD_J2STORE_CHART_MONTHLY_SALES_REPORT');
	$daily_title = JText::_('MOD_J2STORE_CHART_DAILY_SALES_REPORT');
	if($chart_type=='daily'){

		$script .=']);
		//day chart options.
		var dayoptions = {
		title:"'.$daily_title.'",
		pointSize: 6,
		height:300,
		backgroundColor: "#F7F7F7",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],
		vAxis:{
				title:"'.JText::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"#444444"},
				baselineColor: "#ffffff",
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},

		};

		//day line chart.

		var daycharts = new google.visualization.LineChart(document.getElementById("daily_report"));
		daycharts.draw(daychart, dayoptions);

		}
		';
	}elseif($chart_type=='monthly'){

		$script .=']);
		//month chart options.
		var monthoptions = {

		title: "'.$monthly_title.'",
		pointSize: 6,
		height:300,
		backgroundColor: "#F7F7F7",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],
		vAxis:{
				title:"'.JText::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"blue"},
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},
		};

		//month line chart.
		var monthcharts = new google.visualization.LineChart(document.getElementById("monthly_report"));
		monthcharts.draw(monthchart,monthoptions);
		}
		';
	}elseif($chart_type=='yearly'){

		$script .=']);
		//year chart options.

		var yearoptions = {

		title:"'.$year_title .'",
		pointSize: 6,
		height:300,
		backgroundColor: "#F7F7F7",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],

		hAxis: {
				title: "'.JText::_('J2STORE_CHART_YEAR').'",
				titleTextStyle: {color: "blue"},
				format:"#"
				},
		vAxis:{
				title:"'.JText::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"green"},
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},
		};


		//year line chart.

		var yearcharts = new google.visualization.LineChart(document.getElementById("yearly_report"));
		yearcharts.draw(yearchart, yearoptions);

		}
		';


	}
//script declaration.
$document->addScriptDeclaration($script);

?>
<?php if(!empty($days)):?>
<div id="daily_report" class="mod-j2store-daily-sale-chart"></div>
<?php endif;?>
<!--
<?php if($chart_type && ( empty($days) || empty($months) || empty($years))):?>

<div class="alert alert-warning">
	<h5><?php echo JText::_('J2STORE_CHART_DATA_EMPTY');?></h5>
</div>
<?php endif;?>
-->

<?php if(!empty($months)):?>
	<div id="monthly_report" class="mod-j2store-monthly-sale-chart"></div>
<?php endif;?>

<?php if(!empty($years)):?>
	<div id="yearly_report" class="mod-j2store-yearly-sale-shart"></div>
<?php endif;?>


