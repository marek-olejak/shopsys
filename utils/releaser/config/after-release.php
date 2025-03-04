<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\AfterRelease\BeHappyReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckDocsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckPackagesOnPackagistReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnableMergingReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnsureReleaseHighlightsPostIsReleasedReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnsureRoadmapIsUpdatedReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\PostInfoToSlackReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\RemoveLockFilesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetFrameworkBundleVersionToDevReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetMutualDependenciesToDevelopmentVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetPhpImageVersionInDockerfileReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\VerifyInitialBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CheckCorrectReleaseVersionReleaseWorker::class);
    $services->set(VerifyInitialBranchReleaseWorker::class);
    $services->set(CheckUncommittedChangesReleaseWorker::class);
    $services->set(CheckPackagesOnPackagistReleaseWorker::class);
    $services->set(CheckPackagesGithubActionsBuildsReleaseWorker::class);
    $services->set(RemoveLockFilesReleaseWorker::class);
    $services->set(SetFrameworkBundleVersionToDevReleaseWorker::class);
    $services->set(SetMutualDependenciesToDevelopmentVersionReleaseWorker::class);
    $services->set(SetPhpImageVersionInDockerfileReleaseWorker::class);
    $services->set(EnableMergingReleaseWorker::class);
    $services->set(CheckShopsysInstallReleaseWorker::class);
    $services->set(EnsureReleaseHighlightsPostIsReleasedReleaseWorker::class);
    $services->set(PostInfoToSlackReleaseWorker::class);
    $services->set(CheckDocsReleaseWorker::class);
    $services->set(EnsureRoadmapIsUpdatedReleaseWorker::class);
    $services->set(BeHappyReleaseWorker::class);
};
