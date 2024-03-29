<?php

namespace Najm\ModelFormMaker\Commands;

use Illuminate\Console\Command;

use Illuminate\Filesystem\Filesystem;

class ModelFormMaker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modelform:make {ModelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Using laravel artisan can make form based on model class and its attributes, to just submit and create new record.';
    protected $oFile;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileSystem $oFile)
    {
        $this->oFile = $oFile;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sModel = $this->argument('ModelName');

        $sModelClass = '\\App\\Models\\'.$sModel;
        if(!class_exists($sModelClass))
        {
            return $this->error("Model class not found: " . $sModelClass . ", it is required to generate form template"); 
        }

        $oPosts = new $sModelClass;

		$aFields = $oPosts->getConnection()->getSchemaBuilder()->getColumnListing($oPosts->getTable());
		$message = 'No message for now';
		// create form for model
		$sContent =<<<HC
@if(Session::has('fail_message'))
		<div>{{session('fail_message')}}</div>
@endif
        <form method="POST" action="{{url('/')}}/">
            @csrf

HC;
		$aSkipColumns = ['id','updated_at','created_at'];
		foreach($aFields as $k=>$v)
		{
			if(!in_array($v,$aSkipColumns))
			{
				$sContent .= "            <div class='row'>\r\n";
				$sContent .= "                <div class='col'>".$this->stringToHuman($v)."</div>\r\n";
				$sContent .= "                <div class='col'><input name='$v' id='$v' type='text' class=\"@error('$v') is-invalid @enderror\"></div>\r\n";
				$sContent .= "            </div>\r\n";
			}
		}

		$sContent .=<<<HC
			
            <div class='row'>    
                <div class='col'>
                    <input type='submit' name='submit' value='ADD {$this->stringToHuman($oPosts->getTable())}' />
                </div>
            <div>
        </form>

HC;
        
        $sFilename = "form_".$oPosts->getTable().".blade.php";
        $this->saveAsFile($sFilename,$sContent);
		
        return Command::SUCCESS;
    }

    private function stringToHuman($sValue)
    {
        return ucwords(str_replace("_"," ",$sValue));
    }
    private function saveAsFile(string $sFilename,string $sContent,$sPath='')
    {
        $sPath = ($sPath?:resource_path()."/views");

        $sFile = $sPath."/".$sFilename;

        if(!$this->oFile->isDirectory($sPath))
        {
            $this->oFile->makeDirectory($sPath,0644,true,true);    
        }

        if(!$this->oFile->isFile($sFile))
        {
            if($this->oFile->put($sFile,$sContent))
                $this->info("File generated");
            else $this->error("Unable to write file");
        } else {
            if($this->confirm($sFilename . " already exist, overwrite?", true))
            {
                if($this->oFile->put($sFile,$sContent))
                    $this->info("File generated");
            } else {
                return $this->error("File already exist");                
            }
        }
    }
}
