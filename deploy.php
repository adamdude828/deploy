<?php
/*
 * This file has been generated automatically.
 * Please change the configuration for correct use deploy.
 */

//require 'recipe/laravel.php';


env('release_path', '/var/www');
env('project', 'blog');
env('bin/php', '/usr/bin/php');
set('repository', 'git@github.com:adamdude828/deploy.git');
option('tag', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'tag to deploy');
env('bin/composer', function() {
	if (commandExist("composer")) {
		return run('which composer')->toString();
	}

	if (empty($composer)) {
        	run("cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}");
        	$composer = '{{bin/php}} {{release_path}}/composer.phar';
    	}
	
});

$random = rand(0, 10000);
task('clone', function() use($random) {
	$repo = get("repository");
	runLocally("cd /tmp/ && git clone $repo {{project}}$random");
});

task('composer',function() use($random) { 
	runLocally("cd /tmp/{{project}}$random && composer install --no-dev --prefer-dist");
});

task('tar', function() use($random) {
	$tag = input()->getOption("tag");
	runLocally("tar -czvf {{project}}-$tag.tar.gz /tmp/{{project}}$random/*");
});


server('production', '104.236.67.103')
	->user("root")
	->identityFile()
	->env("deploy_path", "/var/www/blog.domain.com");

task('prepare-upload', function() {
	run('if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi');
});

task('upload', function() {
	$tag = input()->getOption('tag');
	
	upload("{{project}}-$tag.tar.gz", "/var/www/");
});


task('deploy', [
	'clone',
	'composer',
	'tar',
	'prepare-upload',
	'upload'
]);



/*

// Set configurations
set('repository', 'git@github.com:adamdude828/deploy.git');
set('shared_files', ['.env']);
set('shared_dirs', [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);
set('writable_dirs', ['bootstrap/cache', 'storage']);

// Configure servers
server('production', '104.236.67.103')
    ->user('root')
  j  ->onityFile('~/.ssh/id_rsa.pub', '~/.ssh/id_rsa', '')
    ->env('deploy_path', '/var/www/prod.domain.com');

/*server('beta', 'beta.domain.com')
    ->user('username')
    ->password()
    ->env('deploy_path', '/var/www/beta.domain.com'); */

/**
 * Restart php-fpm on success deploy.
 *
task('php-fpm:restart', function () {
    // Attention: The user must have rights for restart service
    // Attention: the command "sudo /bin/systemctl restart php-fpm.service" used only on CentOS system
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
//    run('sudo /bin/systemctl restart php-fpm.service');
})->desc('Restart PHP-FPM service');

after('success', 'php-fpm:restart'); */
