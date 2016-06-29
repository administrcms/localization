<?php

namespace Administr\Localization\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminModel extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'administr:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin model class.';

    protected $type = 'Admin model class';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (parent::fire() !== false && $this->option('translated')) {
            $name = $this->getNameInput();

            $this->type = 'Admin model translation class';

            $this->call('administr:model', ['name' => "{$name}Translation"]);
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if( $this->option('translated') )
        {
            return __DIR__.'/stubs/admin_model_translated.stub';
        }

        return __DIR__.'/stubs/admin_model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Models';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the admin model class.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['translated', 't', InputOption::VALUE_NONE, 'Create a new translated admin model.'],
        ];
    }
}