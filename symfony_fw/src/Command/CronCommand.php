<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;

Class CronCommand extends Command {
    // Vars
    protected static $commandName = "app:run-cron";
    
    private $container;
    private $entityManager;
    private $translator;
    
    private $helper;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager, TranslatorInterface $translator) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        
        parent::__construct();
        
        $this->helper = new Helper($this->container, $this->entityManager, $this->translator);
        $this->query = $this->helper->getQuery();
    }
    
    protected function configure() {
        $this->setDescription("Microservice cron.")
            ->setHelp("Update last excution in database.")
            ->addArgument("id", InputArgument::REQUIRED, "Cron id");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write("Start\n");
        
        $id = $input->getArgument("id") != null ? intval($input->getArgument("id")) : 0;
        
        if ($id > 0) {
            $this->query->updateMicroserviceCronDatabase($id);
            
            $output->write("UPDATE\n");
        }
        
        $output->write("End\n\n");
        
        return 0;
    }
    
    // Functions private
}