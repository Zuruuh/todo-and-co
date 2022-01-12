# Contribute to our project

Follow this guide to contribute to Todo&amp;Co.

## Step 1: Knowing what you will improve

Before even starting to work on the project, you need to know which features you will be working on. First thing to do is to check the open issues on gitlab. Find an issue no one is working on right now, and claim it by replying to the issue. You may also ask questions if anything seems unclear before starting. Once your issue is claimed and a project maintainer has given you an approval to work on it, you can get to the next step.

## Step 2: Forking the project

To start working locally on the project, you will need to clone it on your computer. But a simple `git clone` will not suffice, as you do not have the required authorizations to push on the original repository. To "bypass" this restriction, you'll need to create what is called a **fork** of the project you are working on. This will create a copy of the project on your own gitlab account, which will permit you to push as you wish. Once your fork is created, you can clone it to your local computer and create a new branch. Make sure to respect the branch naming conventions of the repository, and to use a name corresponding to the issue you will be working on.

## Step 3: Install the project

To simplify the workflow on different environments, the project has been containerized using docker. To get started with the project, refer to `Installation` chapter of the [readme.md](../readme.md) file of the project. Once your project is up and running, you should be able to start the tests with no failures using the `make test` command. If that is the case, you can finally start working on the code itself.

## Step 4: Coding the feature

Now, you finally get to code the feature you picked up. Make sure to respect the coding guidelines (psr-4) , and commit styling (use [gitmoji](https://gitmoji.dev)) of the project. Once you think your feature is done and good to be merge, you will be asked to validate 3 checks, which are:

- Pipeline passing with as least errors as possible:
  - The project comes with a gitlab CI pipeline to validate the coding conventions of your code. Make sure that it is passing before getting to the next step.
- Feature is being tested:
  - The feature you just added must be tested using phpunit, mink, or both (depends on the kind of feature). This will ensure that your code works so maintainers won't have to check manually.
- All the other tests are still passing:
  - Final check is that you can still run a `make test` without any test failing. If any of the tests fail, you will need to find why and fix it if it is related to your feature.

## Step 5: Creating a merge request

When all the previous checks are good, and you think your feature is ready to be added to the project, you can create a merge request from your repo's branch to the `dev` branch of the original repo. A final pipeline will run upon it's creation, and maintainers will be notified of your request so they can review it, and finally, merge it to the original repo !
