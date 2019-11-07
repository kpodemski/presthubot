<?php
namespace Console\App\Command;

use DateInterval;
use DateTime;
use Github\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
 
class GithubStatsCommand extends Command
{
    /**
     * @var Client;
     */
    protected $client;

    protected function configure()
    {
        $this->setName('github:stats')
            ->setDescription('Stats Github')
            ->addOption(
                'ghtoken',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED
            );
        
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = new Client();
        $ghToken = $input->getOption('ghtoken');
        if (!empty($ghToken)) {
            $this->client->authenticate($ghToken, null, Client::AUTH_URL_TOKEN);
        }

        // Get Stats
        $this->getStats($input, $output);
    }

    private function getStats(InputInterface $input, OutputInterface $output)
    {
        $rows[] = [new TableCell('<info> Stats </info>', ['colspan' => 2])];
        $rows[] = new TableSeparator();
        $repository = $this->client->api('repo')->show('PrestaShop', 'PrestaShop');

        $rows[] = ['# Stars', $repository['stargazers_count']];
        $rows[] = ['# Issues', $repository['open_issues_count']];

        $openPullRequests = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:open');
        $rows[] = ['# PR', $openPullRequests['total_count']];

        if ($input->getOption('username')) {
            $rows[] = new TableSeparator();
            $rows[] = [new TableCell('<info> @'.$input->getOption('username'). '</info>', ['colspan' => 2])];
            $rows[] = new TableSeparator();
    
            $openPullRequests = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:open author:'.$input->getOption('username'));
            $rows[] = ['# PR Open', $openPullRequests['total_count']];
            $mergedPullRequests = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:merged author:'.$input->getOption('username'));
            $rows[] = ['# PR Merged', $mergedPullRequests['total_count']];
            $closedPullRequests = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:closed is:unmerged author:'.$input->getOption('username'));
            $rows[] = ['# PR Closed', $closedPullRequests['total_count']];
            $rows[] = new TableSeparator();
    
            $openIssues = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:issue is:open author:'.$input->getOption('username'));
            $rows[] = ['# Issue Author Open', $openIssues['total_count']];
            $closedIssues = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:issue is:closed author:'.$input->getOption('username'));
            $rows[] = ['# Issue Author Closed', $closedIssues['total_count']];
            $rows[] = new TableSeparator();
    
            $openIssues = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:issue is:open assignee:'.$input->getOption('username'));
            $rows[] = ['# Issue Assignee Open', $openIssues['total_count']];
            $closedIssues = $this->client->api('search')->issues('repo:PrestaShop/PrestaShop is:issue is:closed assignee:'.$input->getOption('username'));
            $rows[] = ['# Issue Assignee Closed', $closedIssues['total_count']];
        }


        $table = new Table($output);
        $table->setRows($rows);
        $table->setStyle('box-double');
        $table->render();
    }
}