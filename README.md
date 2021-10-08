[Burge Affairs Tracking System](http://burge.eu/category-4/BurgeATS)
##	[An Open Source Customer Relationship Management System, Affairs Tracking System, and Ticketing & Messaging System  based on BurgeCMF](http://burge.eu/category-4/BurgeATS)

![BurgATS Logo](http://burge.eu/upload/cat-4-BurgeATS/logo_back_white.jpg)

## Features
* Allows you not only to manage your customers, but also manage and coordinate your employees, allocating different tasks to different persons, and review the results.
* MVC-L architecture of BurgeCMF and CodeIgniter,
* Multi language admin and custoemr environments,
* The **Customer Manager** module allows adding and editing customer properties, tracking customer logs, and executing tasks.
* Customer manager module includes a logging system that creates a file for each event of customer and stores it in JSON format. 
* The **Task Manager** module declares each task. Each task has a specific class that is called by the system scheduler to specify the users should respond to the task.
* The **Task Execution** module executes a task for a user and creates its log. some executions require manger note.
* For each task, it is possible to set users, who execute that task, and also managers who can consider and write notes for that task.
* Each customer has some events (e.g. requested urgent call, or having an unread email), which indicates a supporting action is required to be performed for that customer. These events can be defined in the customer manager module and a task is defined to check if a customer's flag has been raised and asks a user to consider the events.
* The **Ticketing and Messaging System (TMS)** defined as *Message Manager* module allows messaging between different parts of organizations including departments, customers, and users. It also allows adding new participants from another departments to a message, intra-organization commenting on messages (private to the customers), and specifying access level of each user.
* The **Email and SMS Manager** allows sending email/SMS to customers synchronously or asynchronously through the cron job.
* The **Newsletter** module provides defining and sending newsletter.
* Social media login/signup (OAUTH2): Yahoo!, MSN Live, Facebook, Linkedin, Google.

## License
* GNU GPL2
* Review LICENSE file
