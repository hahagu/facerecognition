<?php
/**
 * @copyright Copyright (c) 2019, Matias De lellis <mati86dl@gmail.com>
 *
 * @author Matias De lellis <mati86dl@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\FaceRecognition\Service;

use OCP\IConfig;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCA\FaceRecognition\Service\SettingsService;

class ModelService {

	/** @var IConfig */
	private $config;

	/** @var IAppData */
	private $appData;

	/** @var IRootFolder */
	private $rootFolder;

	/** @var string */
	private $modelsFolder;

	/** @var bool */
	private $customModelPathSet = false;

	public function __construct(IConfig     $config,
	                            IAppData    $appData,
	                            IRootFolder $rootFolder) {
		$this->config     = $config;
		$this->appData    = $appData;
		$this->rootFolder = $rootFolder;
		$this->modelsFolder = getModelPath();

		// Prepare Model Folder
		prepareModelFolder();
	}

	/**
	 * @return string
	 */
	private function getModelPath(): string {
		// Get model path setting
		$modelPath = $this->settingsService->getModelPath();
		
		// Check if model path is null
		if (!is_null($modelPath)) {
			$this->customModelPathSet = true;
			return $modelPath;
		}
		
		// Get this folder
		$instanceId = $this->config->getSystemValue('instanceid', null);
		$appData = $this->rootFolder->get('appdata_'.$instanceId)->getPath();
		$dataDir = $this->config->getSystemValue('datadirectory', null);
		
		$this->customModelPathSet = false;
		return $dataDir . $appData . '/facerecognition/models/';
	}

	/**
	 * @return string
	 */
	public function getFileModelPath(int $modelId, string $file): string {
		return $this->modelsFolder . $modelId . '/' . $file;
	}

	/**
	 * @return bool
	 */
	public function modelFileExists(int $modelId, string $file): bool {
		return file_exists($this->getFileModelPath($modelId, $file));
	}

	/**
	 * @return void
	 */
	public function prepareModelFolder(int $modelId) {
		if ($this->customModelPathSet) {
			if (!is_dir($this->modelsFolder)) {
				mkdir($this->modelsFolder . '/' . $modelID, 0770, true);
			}
		} else {
			try {
				$this->appData->getFolder('/facerecognition/models/' . $modelId);
			} catch (NotFoundException $e) {
				$this->appData->newFolder('/facerecognition/models/' . $modelId);
			}
		}
	}
}