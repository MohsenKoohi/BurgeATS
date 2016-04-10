Note that $CI is your CI instance which can be grabbed by &get_instance() , or if you are in your controller just use $this instead of $CI


-- Logging
$CI->logger->emergency/alert/critical/error/warning/notic/info/debug($message, $context=array());


-- Language
- $CI->language->get(); 							// returns the selected language
- $CI->language->get_default_language();
- $CI->language->get_languages();
- BurgeCMF now supports Persian, and English languages, if you want add a new language, you just need to add a directory to the language directory and translate files from Persian or English to your new language.


-- Controller
- Each controller should extend Burge_CMF_Controller not CI_Controller
- $CI->user returns the User object related to the admin user logged and has access to current page


-- Module Manager
- Note that all modules are located in models folder, in fact these are managers we have in system, and just one sample of a type of each manager is created by system, and thus we use singleton system of CI for these managers.
- Module manager installs a table which has three columns:
	1) module_id which is the abbreviated name of the module, for example user_manager_module has "user" module_id.
	2) sort_order
	3) model_name which is the name of model file of the module substred by "_model" (;), for example hit_counter_module has "hit_counter" model_name. model_name is used to find the module by the framework and call its methods such as get_dashboard_info().
- We have some pseudo modules, which are added as module to module_manager but with no model_name. For example "change_pass", and "dashboard" modules as pseduo modules, which we insert in to module manager, since we want them to be controlled by access manager and also can be accessed from admin pages. But they haven't an independent module manager. For example, dashboard is just a controller page which gathers information from all modules, and also change_pass is a page controlled by user manager module, but it should be independent from it. Thus we added them as pseudo module to the framework. 


# Message Manager
Message manager is responsible for messages between users, between customers, and between users and customers.

## Types of Messages
1. C2C: customer to customer
2. C2D: customer to department
3. D2C: department to customer (a user sends message to a customer on behalf of its department)
4. U2U: user to user

## Types of Message Users
1. supervisor: he can see all messages to/from all users 
2. verifier: can verify customer2customer messages for his departments
3. user with access to customers module will have access to customer/department to customer/department messages.

**message_reply_id** of a parent message will be set to NULL when the sender adds another message,
indicating the message hasn't been responded. 

Note that we don't have a table for tickets and another table for messages in a ticket, instead, we
use message_parent_id field to connect messages, and the first message is called **parent message**.

**message_verifier_id** field is used to specify:

1. user who has verified a customer 2 customer message
2. user who has sent a message on behalf of a department for department 2 customer messages
