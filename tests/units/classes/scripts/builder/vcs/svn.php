<?php

namespace mageekguy\atoum\tests\units\scripts\builder\vcs;

use
	mageekguy\atoum,
	mageekguy\atoum\mock,
	mageekguy\atoum\scripts\builder\vcs
;

require_once(__DIR__ . '/../../../../runner.php');

class svn extends atoum\test
{
	public function beforeTestMethod($testMethod)
	{
		if (extension_loaded('svn') === false)
		{
			define('SVN_REVISION_HEAD', -1);
			define('PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS', 1);
			define('SVN_AUTH_PARAM_DEFAULT_USERNAME', 2);
			define('SVN_AUTH_PARAM_DEFAULT_PASSWORD', 3);
		}
	}

	public function testClass()
	{
		$this->assert
			->testedClass->isSubclassOf('mageekguy\atoum\adapter\aggregator')
		;
	}

	public function test__construct()
	{
		$svn = new vcs\svn();

		$this->assert
			->object($svn->getAdapter())->isInstanceOf('mageekguy\atoum\adapter')
			->variable($svn->getRepositoryUrl())->isNull()
		;

		$svn = new vcs\svn($adapter = new atoum\adapter());

		$this->assert
			->object($svn->getAdapter())->isIdenticalTo($adapter)
			->variable($svn->getRepositoryUrl())->isNull()
		;
	}

	public function testSetAdapter()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$this->assert
			->object($svn->setAdapter($adapter = new atoum\adapter()))->isIdenticalTo($svn)
			->object($svn->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testSetRepositoryUrl()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$this->assert
			->object($svn->setRepositoryUrl($url = uniqid()))->isIdenticalTo($svn)
			->string($svn->getRepositoryUrl())->isEqualTo($url)
			->object($svn->setRepositoryUrl($url = rand(- PHP_INT_MAX, PHP_INT_MAX)))->isIdenticalTo($svn)
			->string($svn->getRepositoryUrl())->isEqualTo((string) $url)
		;
	}

	public function testSetRevision()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$this->assert
			->object($svn->setRevision($revisionNumber = rand(1, PHP_INT_MAX)))->isIdenticalTo($svn)
			->integer($svn->getRevision())->isEqualTo($revisionNumber)
			->object($svn->setRevision($revisionNumber = uniqid()))->isIdenticalTo($svn)
			->integer($svn->getRevision())->isEqualTo((int) $revisionNumber)
		;
	}

	public function testSetUsername()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$this->assert
			->object($svn->setUsername($username = uniqid()))->isIdenticalTo($svn)
			->string($svn->getUsername())->isEqualTo($username)
			->object($svn->setUsername($username = rand(- PHP_INT_MAX, PHP_INT_MAX)))->isIdenticalTo($svn)
			->string($svn->getUsername())->isEqualTo((string) $username)
		;
	}

	public function testSetPassword()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$this->assert
			->object($svn->setPassword($password = uniqid()))->isIdenticalTo($svn)
			->string($svn->getPassword())->isEqualTo($password)
			->object($svn->setPassword($password = rand(- PHP_INT_MAX, PHP_INT_MAX)))->isIdenticalTo($svn)
			->string($svn->getPassword())->isEqualTo((string) $password)
		;
	}

	public function testGetWorkingDirectoryIterator()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$svn->setWorkingDirectory(__DIR__);

		$this->assert
			->object($recursiveIteratorIterator = $svn->getWorkingDirectoryIterator())->isInstanceOf('recursiveIteratorIterator')
			->object($recursiveDirectoryIterator = $recursiveIteratorIterator->getInnerIterator())->isInstanceOf('recursiveDirectoryIterator')
			->string($recursiveDirectoryIterator->current()->getPathInfo()->getPathname())->isEqualTo(__DIR__)
		;
	}

	public function testGetNextRevisions()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new vcs\svn($adapter);

		$adapter->svn_auth_set_parameter = function() {};

		$this->assert
			->exception(function() use ($svn) {
						$svn->getNextRevisions();
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Unable to get logs, repository url is undefined')
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->never()
				->call('svn_log')->never()
		;

		$svn->setRepositoryUrl($repositoryUrl = uniqid());

		$adapter->svn_auth_set_parameter = function() {};
		$adapter->svn_log = array();
		$adapter->resetCalls();

		$this->assert
			->array($svn->getNextRevisions())->isEmpty()
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_log')->withArguments($repositoryUrl, 1, SVN_REVISION_HEAD)->once()
		;

		$svn->setRevision($revision = rand(1, PHP_INT_MAX));

		$adapter->resetCalls();

		$this->assert
			->array($svn->getNextRevisions())->isEmpty()
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_log')->withArguments($repositoryUrl, $revision, SVN_REVISION_HEAD)->once()
		;

		$adapter->resetCalls();

		$adapter->svn_log = array(uniqid() => uniqid());

		$this->assert
			->array($svn->getNextRevisions())->isEmpty()
			->adapter($adapter)->call('svn_log')->withArguments($repositoryUrl, $revision, SVN_REVISION_HEAD)->once()
		;

		$adapter->resetCalls();

		$adapter->svn_log = array(
			array('rev' => $revision1 = uniqid()),
			array('rev' => $revision2 = uniqid()),
			array('rev' => $revision3 = uniqid())
		);

		$this->assert
			->array($svn->getNextRevisions())->isEqualTo(array($revision1, $revision2, $revision3))
			->adapter($adapter)->call('svn_log')->withArguments($repositoryUrl, $revision, SVN_REVISION_HEAD)->once()
		;
	}

	public function testSetExportDirectory()
	{
		$this->mockGenerator
			->generate('mageekguy\atoum\scripts\builder\vcs\svn')
		;

		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new \mock\mageekguy\atoum\scripts\builder\vcs\svn($adapter);

		$this->assert
			->object($svn->setWorkingDirectory($workingDirectory = uniqid()))->isIdenticalTo($svn)
			->string($svn->getWorkingDirectory())->isEqualTo($workingDirectory)
			->object($svn->setWorkingDirectory($workingDirectory = rand(1, PHP_INT_MAX)))->isIdenticalTo($svn)
			->string($svn->getWorkingDirectory())->isIdenticalTo((string) $workingDirectory)
		;
	}

	public function testExportRepository()
	{
		$this->mockGenerator
			->generate('mageekguy\atoum\scripts\builder\vcs\svn')
		;

		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;

		$svn = new \mock\mageekguy\atoum\scripts\builder\vcs\svn($adapter);

		$svn->getMockController()->cleanWorkingDirectory = $svn;

		$this->assert
			->exception(function() use ($svn) {
						$svn->exportRepository(uniqid());
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Unable to export repository, repository url is undefined')
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->never()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_USERNAME, $svn->getUsername())->never()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $svn->getPassword())->never()
				->call('svn_checkout')->never()
		;

		$svn
			->setRepositoryUrl($repositoryUrl = uniqid())
			->setWorkingDirectory($workingDirectory = __DIR__)
		;

		$adapter->resetCalls();
		$adapter->svn_checkout = false;
		$adapter->svn_auth_set_parameter = function() {};

		$this->assert
			->exception(function() use ($svn) {
						$svn->exportRepository();
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Unable to checkout repository \'' . $repositoryUrl . '\' in directory \'' . $workingDirectory . '\'')
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_USERNAME, $svn->getUsername())->never()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $svn->getPassword())->never()
				->call('svn_checkout')->withArguments($svn->getRepositoryUrl(), $workingDirectory, $svn->getRevision())->once()
			->mock($svn)
				->call('cleanWorkingDirectory')->once()
		;

		$svn
			->setUsername(uniqid())
			->getMockController()->resetCalls()
		;

		$adapter->resetCalls();

		$this->assert
			->exception(function() use ($svn) {
						$svn->exportRepository();
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Unable to checkout repository \'' . $repositoryUrl . '\' in directory \'' . $workingDirectory . '\'')
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_USERNAME, $svn->getUsername())->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $svn->getPassword())->never()
				->call('svn_checkout')->withArguments($svn->getRepositoryUrl(), $workingDirectory, $svn->getRevision())->once()
			->mock($svn)
				->call('cleanWorkingDirectory')->once()
		;

		$svn
			->setPassword(uniqid())
			->getMockController()->resetCalls()
		;

		$adapter->resetCalls();

		$this->assert
			->exception(function() use ($svn) {
						$svn->exportRepository();
					}
				)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Unable to checkout repository \'' . $repositoryUrl . '\' in directory \'' . $workingDirectory . '\'')
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_USERNAME, $svn->getUsername())->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $svn->getPassword())->once()
				->call('svn_checkout')->withArguments($svn->getRepositoryUrl(), $workingDirectory, $svn->getRevision())->once()
			->mock($svn)
				->call('cleanWorkingDirectory')->once()
		;

		$svn->getMockController()->resetCalls();

		$adapter->svn_checkout = true;
		$adapter->resetCalls();

		$this->assert
			->object($svn->exportRepository())->isIdenticalTo($svn)
			->adapter($adapter)
				->call('svn_auth_set_parameter')->withArguments(PHP_SVN_AUTH_PARAM_IGNORE_SSL_VERIFY_ERRORS, true)->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_USERNAME, $svn->getUsername())->once()
				->call('svn_auth_set_parameter')->withArguments(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $svn->getPassword())->once()
				->call('svn_checkout')->withArguments($svn->getRepositoryUrl(), $workingDirectory, $svn->getRevision())->once()
			->mock($svn)
				->call('cleanWorkingDirectory')->once()
		;
	}

	public function testCleanWorkingDirectory()
	{
		$adapter = new atoum\test\adapter();
		$adapter->extension_loaded = true;
		$adapter->unlink = function() {};
		$adapter->rmdir = function() {};

		$this->mockGenerator
			->generate('mageekguy\atoum\scripts\builder\vcs\svn')
			->generate('splFileInfo')
		;

		$svn = new \mock\mageekguy\atoum\scripts\builder\vcs\svn($adapter, $svnController = new mock\controller());

		$svn->setWorkingDirectory($workingDirectory = __DIR__);

		$inode11Controller = new mock\controller();
		$inode11Controller->__construct = function() {};
		$inode11Controller->getPathname = $inode11Path = uniqid();
		$inode11Controller->isDir = false;

		$inode11 = new \mock\splFileInfo($inode11Path, $inode11Controller);

		$inode12Controller = new mock\controller();
		$inode12Controller->__construct = function() {};
		$inode12Controller->getPathname = $inode12Path = uniqid();
		$inode12Controller->isDir = false;

		$inode12 = new \mock\splFileInfo($inode12Path, $inode12Controller);

		$inode1Controller = new mock\controller();
		$inode1Controller->__construct = function() {};
		$inode1Controller->getPathname = $inode1Path = uniqid();
		$inode1Controller->isDir = true;

		$inode1 = new \mock\splFileInfo($inode1Path, $inode1Controller);

		$inode2Controller = new mock\controller();
		$inode2Controller->__construct = function() {};
		$inode2Controller->getPathname = $inode2Path = uniqid();
		$inode2Controller->isDir = false;

		$inode2 = new \mock\splFileInfo($inode2Path, $inode2Controller);

		$inode3Controller = new mock\controller();
		$inode3Controller->__construct = function() {};
		$inode3Controller->getPathname = $inode3Path = uniqid();
		$inode3Controller->isDir = true;

		$inode3 = new \mock\splFileInfo($inode3Path, $inode3Controller);

		$inodeController = new mock\controller();
		$inodeController->__construct = function() {};
		$inodeController->getPathname = $workingDirectory = uniqid();
		$inodeController->isDir = true;

		$inode = new \mock\splFileInfo($workingDirectory, $inodeController);

		$svnController->getWorkingDirectoryIterator = array(
				$inode11,
				$inode12,
				$inode1,
				$inode2,
				$inode3
		);

		$this->assert
			->object($svn->cleanWorkingDirectory())->isIdenticalTo($svn)
			->adapter($adapter)
				->call('unlink')->withArguments($inode11Path)->once()
				->call('unlink')->withArguments($inode12Path)->once()
				->call('rmdir')->withArguments($inode1Path)->once()
				->call('unlink')->withArguments($inode2Path)->once()
				->call('rmdir')->withArguments($inode3Path)->once()
				->call('rmdir')->withArguments($workingDirectory)->never()
		;
	}
}

?>
