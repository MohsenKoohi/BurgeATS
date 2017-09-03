[Burge Affairs Tracking System](http://burge.pro/category-4/BurgeATS)
##	[An Open Source Customer Relationship Management System, Affairs Tracking System, and Ticketing & Messaging System  based on BurgeCMF](http://burge.pro/category-4/BurgeATS)

![BurgATS Logo](http://burge.pro/upload/cat-4-BurgeATS/logo_back_white.jpg)

## Features
* Allows you not only to manage your customers, but also manage and coordinate your employees, allocating different tasks to different persons, and review the results.
* MVC architecture using CodeIgniter
* Multi-language admin and customer environments
* Multi language admin and custoemr environments, very useful for multi-language companies, and organizations.
* **Customer Manager** module, which allows add and edit customer properties, tracking customer logs, and executing tasks.
* Customer manager module includes a powerful logger, which creates a file for each event of customer and stores it in JSON format to the related customer folder. 
* **Task Manager** module, which declares each task. Each task has a specific class which is called by the system scheduler to specify users for which task should be executed.
* **Task Execution** module, which executes a task for a user and creates its log. some executions require manger note.
* For each task, it is possible to set users, who execute that task, and also managers who can consider and note about that task.
* Each customer has some events(for example requires urgent call, or has sent an email), which indicates an action should be done for that customer. These events can be defined in the customer manager and the a task can check if a customer's flag has been raised, and ask a user to consider the events.
* A comprehensive **Ticketing and Messaging System (TMS)**, defined as *Message Manager* module, which allows messaging between different parts of organizations including departments, customers, and users. It also allow adding participants from another departments to a message, in-organization commenting on messages (private to the customers), and specifying access level of each user to messages.
* An  **Email and SMS Manager**, to accept requests to send email/sms to customers from other modules and 
sending email/sms asynchronously through cron job or synchronously.

## License
* GNU GPL2
* Review LICENSE file