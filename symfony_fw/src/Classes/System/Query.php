<?php
namespace App\Classes\System;

class Query {
    // Vars
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function selectUserWithHelpCodeDatabase($helpCode) {
        if (trim($helpCode) != "") {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE help_code IS NOT NULL
                                                    AND help_code = :helpCode");

            $query->bindValue(":helpCode", $helpCode);

            $query->execute();

            return $query->fetch();
        }
        else
            return false;
    }
    
    public function selectRoleUserDatabase($roleIds, $change = false) {
        $roleIdsExplode = explode(",", $roleIds);
        array_pop($roleIdsExplode);
        
        $levels = Array();
        
        foreach ($roleIdsExplode as $key => $value) {
            $query = $this->connection->prepare("SELECT level FROM roles_users
                                                    WHERE id = :value");
            
            $query->bindValue(":value", $value);
            
            $query->execute();
            
            $row = $query->fetch();
            
            if ($change == true)
                $levels[] = ucfirst(strtolower(str_replace("ROLE_", "", $row['level'])));
            else
                $levels[] = $row['level'];
        }
        
        return $levels;
    }
    
    public function selectAllRoleUserDatabase($change = false) {
        $query = $this->connection->prepare("SELECT * FROM roles_users");
        
        $query->execute();
        
        $rows = $query->fetchAll();
        
        if ($change == true) {
            foreach ($rows as &$value) {
                $value = str_replace("ROLE_", "", $value);
                $value = array_map("strtolower", $value);
                $value = array_map("ucfirst", $value);
            }
        }
        
        return $rows;
    }
    
    public function selectSettingDatabase() {
        $query = $this->connection->prepare("SELECT * FROM settings
                                                WHERE id = :id");
        
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectSettingSlackIwDatabase($name) {
        $query = $this->connection->prepare("SELECT * FROM settings_slack_iw
                                                WHERE name = :name
                                                AND active = :active");
        
        $query->bindValue(":name", $name);
        $query->bindValue(":active", true);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingSlackIwDatabase() {
        $query = $this->connection->prepare("SELECT * FROM settings_slack_iw");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectSettingLinePushDatabase($name) {
        $query = $this->connection->prepare("SELECT * FROM settings_line_push
                                                WHERE name = :name
                                                AND active = :active");
        
        $query->bindValue(":name", $name);
        $query->bindValue(":active", true);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingLinePushDatabase() {
        $query = $this->connection->prepare("SELECT * FROM settings_line_push");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectSettingLinePushUserDatabase($type, $value) {
        if ($type == "userId") {
            $query = $this->connection->prepare("SELECT * FROM settings_line_push_user
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":userId", $value);
        }
        else if ($type == "pushName") {
            $query = $this->connection->prepare("SELECT * FROM settings_line_push_user
                                                    WHERE push_name = :pushName");
            
            $query->bindValue(":pushName", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingLinePushUserDatabase($type, $value = "") {
        if ($type == "all")
            $query = $this->connection->prepare("SELECT * FROM settings_line_push_user");
        else if ($type == "allPushName") {
            $query = $this->connection->prepare("SELECT * FROM settings_line_push_user
                                                    WHERE push_name = :pushName");
            
            $query->bindValue(":pushName", $value);
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectLanguageDatabase($code) {
        $query = $this->connection->prepare("SELECT * FROM languages
                                                WHERE code = :code");

        $query->bindValue(":code", $code);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllLanguageDatabase() {
        $query = $this->connection->prepare("SELECT * FROM languages");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageDatabase($language, $id) {
        $query = $this->connection->prepare("SELECT pages.*,
                                                pages_titles.$language AS title,
                                                pages_arguments.$language AS argument,
                                                pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                            WHERE pages.id = :id
                                            AND pages_titles.id = pages.id
                                            AND pages_arguments.id = pages.id
                                            AND pages_menu_names.id = pages.id
                                            ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPageDatabase($language, $search = null) {
        if ($search == null) {
            $query = $this->connection->prepare("SELECT pages.*,
                                                    pages_titles.$language AS title,
                                                    pages_arguments.$language AS argument,
                                                    pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                WHERE pages_titles.id = pages.id
                                                AND pages_arguments.id = pages.id
                                                AND pages_menu_names.id = pages.id
                                                ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        }
        else {
            $query = $this->connection->prepare("SELECT pages.*,
                                                    pages_titles.$language AS title,
                                                    pages_arguments.$language AS argument,
                                                    pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                WHERE pages_titles.id = pages.id
                                                AND pages_arguments.id = pages.id
                                                AND pages_menu_names.id = pages.id
                                                AND pages.only_link = :onlyLink
                                                AND pages.id > :idStart
                                                AND (pages_titles.$language LIKE :search
                                                    OR pages_arguments.$language LIKE :search
                                                    OR pages_menu_names.$language LIKE :search)");
            
            $query->bindValue(":onlyLink", 0);
            $query->bindValue(":idStart", 5);
            $query->bindValue(":search", "%$search%");
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageParentDatabase($parent = null) {
        if ($parent != null) {
            $query = $this->connection->prepare("SELECT * FROM pages
                                                    WHERE parent = :parent
                                                    ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");

            $query->bindValue(":parent", $parent);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM pages
                                                    WHERE parent is NULL
                                                    ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageChildrenDatabase($parent) {
        $query = $this->connection->prepare("SELECT * FROM pages
                                                WHERE parent = :parent");

        $query->bindValue(":parent", $parent);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageCommentDatabase($type, $id, $username = null) {
        if ($type == "single") {
            $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                    WHERE id = :id");
        }
        else if ($type = "reply") {
            $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                    WHERE id_reply = :id
                                                    AND username = :username");
            
            $query->bindValue(":username", $username);
        }
        else if ($type = "edit") {
            $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                    WHERE id_reply = :id");
        }

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
        
    public function selectAllPageCommentDatabase($pageId) {
        $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                WHERE page_id = :pageId");

        $query->bindValue(":pageId", $pageId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectUserDatabase($value) {
        if (is_numeric($value) == true) {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $value);
        }
        else if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE email = :email");
            
            $query->bindValue(":email", $value);
        }
        else {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE username = :username");
            
            $query->bindValue(":username", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllUserDatabase($idExclude = 0) {
        $query = $this->connection->prepare("SELECT * FROM users
                                                WHERE id != :idExclude");
        
        $query->bindValue(":idExclude", $idExclude);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectModuleDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM modules
                                                WHERE id = :id");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllModuleDatabase($id = null, $position = null) {
        if ($id == null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM modules
                                                    WHERE position = :position
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
            
            $query->bindValue(":position", $position);
        }
        else if ($id != null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM modules
                                                    WHERE position = :position
                                                UNION
                                                SELECT * FROM modules
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            $query->bindValue(":position", $position);
        }
        else if ($id == null && $position == null)
            $query = $this->connection->prepare("SELECT * FROM modules");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPaymentDatabase($transaction) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE transaction = :transaction
                                                AND status_delete = :statusDelete");
        
        $query->bindValue(":transaction", $transaction);
        $query->bindValue(":statusDelete", 0);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPaymentDatabase($userId) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE user_id = :userId
                                                AND status_delete = :statusDelete");
        
        $query->bindValue(":userId", $userId);
        $query->bindValue(":statusDelete", 0);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectMicroserviceApiDatabase($id, $bypass = false) {
        if ($bypass == false) {
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    WHERE id = :id
                                                    AND active = :active");
            
            $query->bindValue(":active", 1);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    WHERE id = :id");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceApiDatabase($bypass = false) {
        if ($bypass == false) {
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    WHERE active = :active");

            $query->bindValue(":active", 1);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM microservice_api");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectMicroserviceDeployDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM microservice_deploy
                                                WHERE id = :id");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceDeployDatabase() {
        $query = $this->connection->prepare("SELECT * FROM microservice_deploy");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    // Functions private
}