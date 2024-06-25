<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Organisme;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\OrganismeRepository;

#[AsCommand(
    name: 'app:init_base',
    description: 'Add a short description for your command',
)]
class InitBaseCommand extends Command
{
    public function __construct(
        private UserRepository $userR,
        private OrganismeRepository $orgaR
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    public function core()
    {

        $orga = new Organisme();
        $orga->setName("cap3c");


        $this->orgaR->save($orga);

        $user = new User();
        $user->fillAdmin("admin@cap3c.net", $this->userR->hash($user, "1234"), "admin", $orga);

        #$user->setEmail("admin@cap3c.net");
        #$user->setName("admin");
        #$user->setPassword($this->userR->hash($user, "1234"));
        #$user->setRoles(["ROLE_orga_admin"]);
        #$user->setOrganisme($orga);

        $this->userR->save($user, true);

    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->core();
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
