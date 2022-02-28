<?php
define("DIR_ROOT", realpath(dirname(__FILE__).'/../../'));
require_once DIR_ROOT.'/conf/system.conf.php';
error_reporting(ERROR_REPORTING_LEVEL);
require_once DIR_SYSTEM.'/Core/Module.class.php';
require_once DIR_SYSTEM.'/Module/Autoloader.class.php';
$autoloader = new \System\Module\Autoloader();
$journal = \System\Core\Journal::singleton();

class xmlZParser extends \Model\ProjectXMLParser {
	
	public function processXML() {
		$this->processInit();
		$model = \Model\Project::singleton();
		$xml = simplexml_load_file($this->filename);
		$this->setStat('all', count($xml->houses->project));

		$this->setStat('allIdsDB', $this->getCountIdsProjectFromDB());
		
		for($i=0; $i<count($xml->houses->project); $i++) {
			$this->project_new = $xml->houses->project[$i];
			$this->project_fut = array();
			$this->fotos_fut = array();
			
			$this->journal->logConsole('[] analizuje projekt - i='.$i);
			
			if($this->project_cur = $model->clearAll()->addBind('xml_name', $this->project_new->name)->where('name=:xml_name')
														->addBind('workshop_id', $this->getWorkshopId())->where('workshop_id=:workshop_id')
														->select()) {
				$this->unsetIdFromDB($this->project_cur['id']);
				$this->journal->logConsole('znalazlo projekt - i='.$i.', name='.$this->project_new->name);
				$this->increaseStat('found');
			} else {
				$this->journal->logConsole('NIE znalazlo projektu - i='.$i.', name='.$this->project_new->name);
				$this->increaseStat('new');
			}

			if(!empty($this->project_new->name)) {
				$this->project_fut['external_name'] = (string)$this->project_new->name;
			}
			if(!empty($this->project_new->price)) {
				$this->project_fut['price'] = (float)$this->project_new->price;
			}
			if(!empty($this->project_new->name)) {
				$this->project_fut['name'] = (string)$this->project_new->name;
			}
			if(!empty($this->project_new->categories)) {
				$this->project_fut['building_type'] = 2;
				foreach($this->project_new->categories->category as $k=>$v) {
					if ($v->attributes()->id == '1'){
						$this->project_fut['building_development_type'] = 2;
						$this->project_fut['building_type'] = 2;
					}
					if ($v->attributes()->id == '11'){
						$this->project_fut['architectural_style'] = 2;
					
					}
					if ($v->attributes()->id == '3'){
						$this->project_fut['architectural_style'] = 4;
						
					}
					if ($v->attributes()->id == '51'){
						$this->project_fut['building_development_type'] = 4;
					}
					if ($v->attributes()->id == '52'){
						$this->project_fut['building_type'] = 4;
					}
					if ($v->attributes()->id == '76'){
						$this->project_fut['building_technology'] = 8;
					}
					if ($v->attributes()->id == '85'){
						$this->project_fut['building_type'] = 32;
					}
					if($v->attributes()->id == '55') {
						$this->project_fut['heat_consumption'] = 4;
					}
					
					
				// $this->project_fut['name'] = (string)$this->project_new->name;
				}
				
			}
			
			if (!isset($this->project_fut['architectural_style'])) {
						$this->project_fut['architectural_style'] = 2;
					}
			
			if(!empty($this->project_new->technology->values->value)){
				if((int)$this->project_new->technology->values->value == 1){
					$this->project_fut['building_technology'] = 4;
				}
				if($this->project_new->technology->values->value == 2){
					$this->project_fut['building_technology'] = 2;
				}
				if($this->project_new->technology->values->value == 3){
					$this->project_fut['building_technology'] = 8;
				}
				
			}
			if(!empty($this->project_new->usable_area)) {
				$this->project_fut['living_area'] = (float)$this->project_new->usable_area;
			}
			if(!empty($this->project_new->footprint_area)) {
				$this->project_fut['building_area'] = (float)$this->project_new->footprint_area;
			}
			if(!empty($this->project_new->roof_angle)) {
				$this->project_fut['roof_angle'] = (float)$this->project_new->roof_angle;
			}
			if(!empty($this->project_new->roof_area)) {
				$this->project_fut['roof_area'] = (float)$this->project_new->roof_area;
			}
			if(!empty($this->project_new->roof_type)) {
				if ($this->project_new->roof_type == '1') $this->project_fut['roof_type'] = 8;
				if ($this->project_new->roof_type == '2') $this->project_fut['roof_type'] = 32;
				if ($this->project_new->roof_type == '4') $this->project_fut['roof_type'] = 16;
				if ($this->project_new->roof_type == '8') $this->project_fut['roof_type'] = 4;
				
			}
			if(!empty($this->project_new->height)) {
				$this->project_fut['building_height'] = (float)$this->project_new->height;
			}
			if(!empty($this->project_new->PowGaraz)) {
				$this->project_fut['garage_area'] = (float)$this->project_new->PowGaraz;
			}
			if(!empty($this->project_new->Garaz) && $this->project_new->Garaz >= 2) {
				$this->project_fut['garage_quantity'] = $this->project_new->Garaz-1;
			}
			if(!empty($this->project_new->lot_length)) {
				$this->project_fut['min_plot_size1'] = (float)$this->project_new->lot_length;
			}
			if(!empty($this->project_new->lot_width)) {
				$this->project_fut['min_plot_size2'] = (float)$this->project_new->lot_width;
			}
			if(!empty($this->project_new->Piwnica)) {
				$this->project_fut['basement'] = (int)$this->project_new->Piwnica;
			}
			if(!empty($this->project_new->volume)) {
				$this->project_fut['cubature'] = (float)$this->project_new->volume;
			}
			if(!empty($this->project_new->Energooszczedny) && $this->project_new->Energooszczedny == 1) {
				$this->project_fut['heat_consumption'] = 4;
			}
			if(!empty($this->project_new->description)) {
				$this->project_fut['description'] = (string)$this->project_new->description;
			}
			if(!empty($this->project_new->TechnologiaOpis)) {
				$this->project_fut['technology_description'] = (string)$this->project_new->TechnologiaOpis;
			}
			
			if(!empty($this->project_new->costs->Stan_zerowy)) {
				$this->project_fut['stan_zerowy'] = (float)$this->project_new->costs->Stan_zerow;
			}
			
			if(!empty($this->project_new->costs->average->finishing_works_cost)) {
				$this->project_fut['roboty_wykonczeniowe_wewn'] = (float)$this->project_new->costs->average->finishing_works_cost;
			}
			if(!empty($this->project_new->costs->average->total_cost)) {
				$this->project_fut['total_cost_calculation'] = (float)$this->project_new->costs->average->total_cost;
			}
			if(!empty($this->project_new->garage->size)) {
				$this->project_fut['garage_quantity'] = (int)$this->project_new->garage->size;
			}
			if(!empty($this->project_new->storeys)) {
				if($this->project_new->storeys->attributes()->count == '1') {
					$this->project_fut['building_development_type'] = 2;
				}
				if($this->project_new->storeys->attributes()->count == '1+') {
					$this->project_fut['building_type'] = 2;
				}
				if($this->project_new->storeys->attributes()->count == '2') {
					$this->project_fut['building_development_type'] = 8;
				
				}if($this->project_new->storeys->attributes()->count == '2-') {
					$this->project_fut['building_development_type'] = 4;
				}
			}
			if(!empty($this->project_new->images->visualizations)) {
				foreach($this->project_new->images->visualizations as $k=>$v) {
					if(!empty($v->visualization)) {
						foreach($v->visualization as $kk=>$vv) {
								
								$this->fotos_fut['fotos_visualizations'][] = (string)$vv->url;
						}
					}
				}
			}
			if(!empty($this->project_new->images->projections)) {
				foreach($this->project_new->images->projections as $k=>$v) {
					if(!empty($v->projection)) {
						foreach($v->projection as $kk=>$vv) {
								
								$this->fotos_fut['fotos_plans'][] = (string)$vv->url;	
						}
					}
				}
			}
			if(!empty($this->project_new->images->elevations)) {
				foreach($this->project_new->images->elevations as $k=>$v) {
					if(!empty($v->elevation)) {
						foreach($v->elevation as $kk=>$vv) {
								
								$this->fotos_fut['fotos_elevations'][] = (string)$vv->url;	
						}
					}
				}
			}
			if(!empty($this->project_new->storeys)) {
				$ii = 0;
				$this->project_fut['rooms'] = 0;
				foreach($this->project_new->storeys->storey as $k=>$v) {
					$this->project_fut['plans_descriptions']['name'][$ii] = '';
					$this->project_fut['plans_descriptions']['content'][$ii] = '';
					
					
					if ($v->attributes()->type == 0){
						$this->project_fut['plans_descriptions']['name'][$ii] = 'piwnica';
					}
					if ($v->attributes()->type == 1){
							$this->project_fut['plans_descriptions']['name'][$ii] = 'parter';
					}
					if ($v->attributes()->type == 2){
							$this->project_fut['plans_descriptions']['name'][$ii] = 'piętro';
						
					}
					if ($v->attributes()->type == 3){
							$this->project_fut['plans_descriptions']['name'][$ii] = 'poddasze';
						
					}
						
					
					$iii = 1;
					
					foreach($v->rooms->room as $kkkk=>$vvvv) {	
						if(!empty($vvvv->name)) {
							$this->project_fut['plans_descriptions']['content'][$ii] .= $iii.'. '.(string)$vvvv->name.': '.(string)$vvvv->area.' m<sup>2</sup>'."\r\n";
							$iii++;
						}
					if(!empty($vvvv->attributes()->regular)) {
						
						$this->project_fut['rooms'] = $this->project_fut['rooms']+(int)$vvvv->attributes()->regular;
					}
						
					}
					$ii++;
				}
					
			}
			
		
			if($this->getUpdateMode()) {
				$fotos_return_array = $this->updateFotos();
			}
			
//			$this->showRecordDifferences();
			
			if($this->getUpdateMode()) {
				if($this->project_cur) {
					$this->increaseStat('mod');
					$this->project_fut['id'] = $this->project_cur['id'];
					
					if(!empty($this->project_cur['fotos_plot_plans'])) {
						$this->project_fut['fotos_plot_plans'] = $this->project_cur['fotos_plot_plans'];
					}
					if(!empty($this->project_cur['fotos_plot_plans_mirror'])) {
						$this->project_fut['fotos_plot_plans_mirror'] = $this->project_cur['fotos_plot_plans_mirror'];
					}
					if(!empty($this->project_cur['fotos_profiles'])) {
						$this->project_fut['fotos_profiles'] = $this->project_cur['fotos_profiles'];
					}
					if(!empty($this->project_cur['fotos_profiles_mirror'])) {
						$this->project_fut['fotos_profiles_mirror'] = $this->project_cur['fotos_profiles_mirror'];
					}
				} else {
					$this->increaseStat('add');
					$this->project_fut['workshop_id'] = $this->getWorkshopId();
					$this->project_fut['time_create'] = time();
				}
				$res = $model->clearAll()->insert(
					$this->project_fut,
					$fotos_return_array['fotos_visualizations'],
					NULL,
					$fotos_return_array['fotos_plans'],
					NULL,
					NULL,
					NULL,
					$fotos_return_array['fotos_elevations'],
					NULL,
					NULL,
					NULL,
					$fotos_return_array['fotos_realizations'],
					NULL
				);
				if($model->getAffectedRows($res) == 2)
					$this->increaseStat('mod_affected');
				elseif($model->getAffectedRows($res) == 1)
					$this->increaseStat('new_affected');
				elseif($model->getAffectedRows($res) == 0)
					$this->increaseStat('no_affected');
			}
		}
	}
}
			
$XML = new xmlZParser();
if(!empty($argv[3]) && $argv[3] == 'doUpdate') {
	$XML->setUpdateMode(1);
}

if(!empty($argv[1])) {
	switch($argv[1]) {
		case 'processXML':
			if(!empty($argv[2])) {
				$XML->setWorkshopId(88)->setFilename($argv[2]);
				$XML->processXML();
				$XML->showStats();
				exit;
			}
			break;
	}
}
$journal->logConsole('Use one of argument: processXML(filename, ["doUpdate"|null])');

?>
