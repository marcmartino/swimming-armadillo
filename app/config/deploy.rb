set :application, "happystats"
set :domain,      "happystats.io"
set :deploy_to,   "/var/www/#{application}"
set :user, "root"

set :repository,  "git@github.com:marcmartino/swimming-armadillo.git"
set :scm,         :git

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server
set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor", app_path + "/sessions"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true
set :use_composer, true
set :update_vendors, true
set :composer_options,  "--prefer-dist"
set :dump_assetic_assets, true

set  :keep_releases,  5

logger.level = Logger::MAX_LEVEL
