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
            $query = $this->connection->prepare("SELECT * FROM user
                                                    WHERE help_code IS NOT NULL
                                                    AND help_code = :helpCode
                                                    ORDER by username ASC");

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
            $query = $this->connection->prepare("SELECT level FROM role_user
                                                    WHERE id = :value
                                                    ORDER by level ASC");
            
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
        $query = $this->connection->prepare("SELECT * FROM role_user
                                                ORDER by level ASC");
        
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
        $query = $this->connection->prepare("SELECT * FROM setting
                                                WHERE id = :id");
        
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectSettingSlackIwDatabase($name) {
        $query = $this->connection->prepare("SELECT * FROM setting_slack_iw
                                                WHERE name = :name
                                                AND active = :active
                                                ORDER by name ASC");
        
        $query->bindValue(":name", $name);
        $query->bindValue(":active", true);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingSlackIwDatabase() {
        $query = $this->connection->prepare("SELECT * FROM setting_slack_iw
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectSettingLinePushDatabase($name) {
        $query = $this->connection->prepare("SELECT * FROM setting_line_push
                                                WHERE name = :name
                                                AND active = :active
                                                ORDER by name ASC");
        
        $query->bindValue(":name", $name);
        $query->bindValue(":active", true);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingLinePushDatabase() {
        $query = $this->connection->prepare("SELECT * FROM setting_line_push
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectSettingLinePushUserDatabase($type, $value) {
        if ($type == "userId") {
            $query = $this->connection->prepare("SELECT * FROM setting_line_push_user
                                                    WHERE user_id = :userId
                                                    ORDER by push_name ASC");
            
            $query->bindValue(":userId", $value);
        }
        else if ($type == "pushName") {
            $query = $this->connection->prepare("SELECT * FROM setting_line_push_user
                                                    WHERE push_name = :pushName
                                                    ORDER by push_name ASC");
            
            $query->bindValue(":pushName", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllSettingLinePushUserDatabase($type, $value = "") {
        if ($type == "all")
            $query = $this->connection->prepare("SELECT * FROM setting_line_push_user
                                                    ORDER by push_name ASC");
        else if ($type == "allPushName") {
            $query = $this->connection->prepare("SELECT * FROM setting_line_push_user
                                                    WHERE push_name = :pushName
                                                    ORDER by push_name ASC");
            
            $query->bindValue(":pushName", $value);
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectLanguageDatabase($code) {
        $query = $this->connection->prepare("SELECT * FROM language
                                                WHERE code = :code");

        $query->bindValue(":code", $code);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllLanguageDatabase() {
        $query = $this->connection->prepare("SELECT * FROM language");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageDatabase($language, $id) {
        $query = $this->connection->prepare("SELECT page.*,
                                                page_title.$language AS title,
                                                page_argument.$language AS argument,
                                                page_menu_name.$language AS menu_name
                                                FROM page, page_title, page_argument, page_menu_name
                                            WHERE page.id = :id
                                            AND page_title.id = page.id
                                            AND page_argument.id = page.id
                                            AND page_menu_name.id = page.id
                                            ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPageDatabase($language, $search = null) {
        if ($search == null) {
            $query = $this->connection->prepare("SELECT page.*,
                                                    page_title.$language AS title,
                                                    page_argument.$language AS argument,
                                                    page_menu_name.$language AS menu_name
                                                FROM page, page_title, page_argument, page_menu_name
                                                WHERE page_title.id = page.id
                                                AND page_argument.id = page.id
                                                AND page_menu_name.id = page.id
                                                ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        }
        else {
            $query = $this->connection->prepare("SELECT page.*,
                                                    page_title.$language AS title,
                                                    page_argument.$language AS argument,
                                                    page_menu_name.$language AS menu_name
                                                FROM page, page_title, page_argument, page_menu_name
                                                WHERE page_title.id = page.id
                                                AND page_argument.id = page.id
                                                AND page_menu_name.id = page.id
                                                AND page.only_link = :onlyLink
                                                AND (page.id = :idStartA OR page.id > :idStartB)
                                                AND (page_title.$language LIKE :search
                                                    OR page_argument.$language LIKE :search
                                                    OR page_menu_name.$language LIKE :search)");
            
            $query->bindValue(":onlyLink", 0);
            $query->bindValue(":idStartA", 2);
            $query->bindValue(":idStartB", 5);
            $query->bindValue(":search", "%$search%");
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageParentDatabase($parent = null) {
        if ($parent != null) {
            $query = $this->connection->prepare("SELECT * FROM page
                                                    WHERE parent = :parent
                                                    ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");

            $query->bindValue(":parent", $parent);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM page
                                                    WHERE parent is NULL
                                                    ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageChildrenDatabase($parent) {
        $query = $this->connection->prepare("SELECT * FROM page
                                                WHERE parent = :parent");

        $query->bindValue(":parent", $parent);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageCommentDatabase($type, $id, $username = null) {
        if ($type == "single") {
            $query = $this->connection->prepare("SELECT * FROM page_comment
                                                    WHERE id = :id");
        }
        else if ($type = "reply") {
            $query = $this->connection->prepare("SELECT * FROM page_comment
                                                    WHERE id_reply = :id
                                                    AND username = :username");
            
            $query->bindValue(":username", $username);
        }
        else if ($type = "edit") {
            $query = $this->connection->prepare("SELECT * FROM page_comment
                                                    WHERE id_reply = :id");
        }

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
        
    public function selectAllPageCommentDatabase($pageId) {
        $query = $this->connection->prepare("SELECT * FROM page_comment
                                                WHERE page_id = :pageId");

        $query->bindValue(":pageId", $pageId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectUserDatabase($value) {
        if (is_numeric($value) == true) {
            $query = $this->connection->prepare("SELECT * FROM user
                                                    WHERE id = :id
                                                    ORDER by username ASC");
            
            $query->bindValue(":id", $value);
        }
        else if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            $query = $this->connection->prepare("SELECT * FROM user
                                                    WHERE email = :email
                                                    ORDER by username ASC");
            
            $query->bindValue(":email", $value);
        }
        else {
            $query = $this->connection->prepare("SELECT * FROM user
                                                    WHERE username = :username
                                                    ORDER by username ASC");
            
            $query->bindValue(":username", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllUserDatabase($idExclude = 0) {
        $query = $this->connection->prepare("SELECT * FROM user
                                                WHERE id != :idExclude
                                                ORDER by username ASC");
        
        $query->bindValue(":idExclude", $idExclude);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectModuleDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM module
                                                WHERE id = :id
                                                ORDER BY COALESCE(position, rank_in_column), rank_in_column");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllModuleDatabase($id = null, $position = null) {
        if ($id == null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM module
                                                    WHERE position = :position
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
            
            $query->bindValue(":position", $position);
        }
        else if ($id != null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM module
                                                    WHERE position = :position
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column
                                                UNION
                                                SELECT * FROM module
                                                    WHERE id = :id
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
            
            $query->bindValue(":id", $id);
            $query->bindValue(":position", $position);
        }
        else if ($id == null && $position == null)
            $query = $this->connection->prepare("SELECT * FROM module
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPaymentDatabase($transaction) {
        $query = $this->connection->prepare("SELECT * FROM payment
                                                WHERE transaction = :transaction
                                                AND status_delete = :statusDelete");
        
        $query->bindValue(":transaction", $transaction);
        $query->bindValue(":statusDelete", 0);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPaymentDatabase($userId) {
        $query = $this->connection->prepare("SELECT * FROM payment
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
                                                    AND active = :active
                                                    ORDER by name ASC");
            
            $query->bindValue(":active", 1);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    WHERE id = :id
                                                    ORDER by name ASC");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceApiDatabase($bypass = false) {
        if ($bypass == false) {
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    WHERE active = :active
                                                    ORDER by name ASC");

            $query->bindValue(":active", 1);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM microservice_api
                                                    ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectMicroserviceDeployDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM microservice_deploy
                                                WHERE id = :id
                                                ORDER by name ASC");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceDeployDatabase() {
        $query = $this->connection->prepare("SELECT * FROM microservice_deploy
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    // Functions private
}