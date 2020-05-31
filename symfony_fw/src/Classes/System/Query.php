<?php
namespace App\Classes\System;

class Query {
    // Vars
    private $helper;
    
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        
        $this->connection = $this->helper->getConnection();
    }
    
    // User
    public function selectWithHelpCodeUserDatabase($helpCode) {
        if (trim($helpCode) != "") {
            $query = $this->connection->prepare("SELECT * FROM user
                                                    WHERE help_code IS NOT NULL
                                                    AND help_code = :helpCode
                                                    ORDER by username ASC");
            
            $query->bindValue(":helpCode", $helpCode);
            
            $query->execute();
            
            return $query->fetch();
        }
        
        return false;
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
    
    public function updateUserDatabase($type, $value, $id, $clientIp = 0, $dateCurrent = null, $dateLast = null, $count = 0) {
        if ($type == "credit") {
            $query = $this->connection->prepare("UPDATE user
                                                    SET credit = :credit
                                                    WHERE id = :id");
            
            $query->bindValue(":credit", $value);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "role") {
            $query = $this->connection->prepare("UPDATE user
                                                SET roles = :roles
                                                WHERE id = :id");
            
            $query->bindValue(":roles", $value);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "success") {
            $query = $this->connection->prepare("UPDATE user
                                                    SET date_current_login = :dateCurrentLogin,
                                                        date_last_login = :dateLastLogin,
                                                        ip = :ip,
                                                        attempt_login = :attemptLogin
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            $query->bindValue(":dateCurrentLogin", $dateCurrent);
            $query->bindValue(":ip", $clientIp);
            $query->bindValue(":dateLastLogin", $dateLast);
            $query->bindValue(":attemptLogin", $count);
            
            return $query->execute();
        }
        else if ($type == "failure") {
            $query = $this->connection->prepare("UPDATE user
                                                    SET date_current_login = :dateCurrentLogin,
                                                        date_last_login = :dateLastLogin,
                                                        ip = :ip,
                                                        attempt_login = :attemptLogin
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            $query->bindValue(":dateCurrentLogin", $dateCurrent);
            $query->bindValue(":ip", $clientIp);
            $query->bindValue(":dateLastLogin", $dateLast);
            $query->bindValue(":attemptLogin", $count);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function deleteUserDatabase($type, $id = 0) {
        if ($type == "one") {
            $this->deletePaymentDatabase("oneUser", $id);
            
            $query = $this->connection->prepare("DELETE FROM user
                                                    WHERE id > :idExclude
                                                    AND id = :id");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $this->deletePaymentDatabase("allUser");
            
            $query = $this->connection->prepare("DELETE FROM user
                                                    WHERE id > :idExclude");
            
            $query->bindValue(":idExclude", 1);
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Role
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
    
    public function deleteRoleUserDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM role_user
                                                    WHERE id > :idExclude
                                                    AND id = :id");
            
            $query->bindValue(":idExclude", 4);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM role_user
                                                    WHERE id > :idExclude");
            
            $query->bindValue(":idExclude", 4);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function deleteFromTableRoleUserDatabase($type, $language, $id = 0) {
        $pageRows = $this->selectAllPageDatabase($language, null, true);
        $userRows = $this->selectAllUserDatabase(1);
        $settingRow = $this->selectSettingDatabase();
        
        if ($type == "one") {
            foreach ($pageRows as $key => $value) {
                $roleExplode = explode(",", $value['role_user_id']);
                
                $key = array_search($id, $roleExplode);
                
                if ($key !== false) {
                    unset($roleExplode[$key]);
                    
                    $roleImplode = implode(",", $roleExplode);
                    
                    $query = $this->connection->prepare("UPDATE page
                                                            SET role_user_id = :roleImplode
                                                            WHERE id = :id");
                    
                    $query->bindValue(":roleImplode", $roleImplode);
                    $query->bindValue(":id", $value['id']);
                    
                    $query->execute();
                }
            }
            
            foreach ($userRows as $key => $value) {
                $roleExplode = explode(",", $value['role_user_id']);
                
                $key = array_search($id, $roleExplode);
                
                if ($key !== false) {
                    unset($roleExplode[$key]);
                    
                    $roleImplode = implode(",", $roleExplode);
                    
                    $roleUserRow = $this->selectRoleUserDatabase($roleImplode);
                    
                    $roleUserImplode = implode(",", $roleUserRow);
                    
                    $query = $this->connection->prepare("UPDATE user
                                                            SET role_user_id = :roleImplode,
                                                                roles = :roleUserImplode
                                                            WHERE id = :id");
                    
                    $query->bindValue(":roleImplode", $roleImplode);
                    $query->bindValue(":roleUserImplode", $roleUserImplode);
                    $query->bindValue(":id", $value['id']);
                    
                    $query->execute();
                }
            }
            
            $roleExplode = explode(",", $settingRow['role_user_id']);
            
            $key = array_search($id, $roleExplode);
            
            if ($key !== false) {
                unset($roleExplode[$key]);
                
                $roleImplode = implode(",", $roleExplode);
                
                $query = $this->connection->prepare("UPDATE setting
                                                        SET role_user_id = :roleImplode
                                                        WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":id", 1);
                
                return $query->execute();
            }
            
            return false;
        }
        else if ($type == "all") {
            foreach ($pageRows as $key => $value) {
                $roleImplode = $this->roleImplode($value['role_user_id']);
                
                $query = $this->connection->prepare("UPDATE page
                                                        SET role_user_id = :roleImplode
                                                        WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":id", $value['id']);
                
                $query->execute();
            }
            
            foreach ($userRows as $key => $value) {
                $roleImplode = $this->roleImplode($value['role_user_id']);
                
                $roleUserRow = $this->selectRoleUserDatabase($roleImplode);
                
                $roleUserImplode = implode(",", $roleUserRow);
                
                $query = $this->connection->prepare("UPDATE user
                                                        SET role_user_id = :roleImplode,
                                                            roles = :roleUserImplode
                                                        WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":roleUserImplode", $roleUserImplode);
                $query->bindValue(":id", $value['id']);
                
                $query->execute();
            }
            
            $roleImplode = $this->roleImplode($settingRow['role_user_id']);
            
            $query = $this->connection->prepare("UPDATE setting
                                                    SET role_user_id = :roleImplode
                                                    WHERE id = :id");
            
            $query->bindValue(":roleImplode", $roleImplode);
            $query->bindValue(":id", 1);
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Setting
    public function selectSettingDatabase() {
        $query = $this->connection->prepare("SELECT *, AES_DECRYPT(server_ssh_password, UNHEX(SHA2(secret_passphrase, 512))) AS server_ssh_password_decrypt,
                                                AES_DECRYPT(server_key_private_password, UNHEX(SHA2(secret_passphrase, 512))) AS server_key_private_password_decrypt
                                                FROM setting
                                                WHERE id = :id");
        
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        return $query->fetch();
    }

    public function updateSettingDatabase($type, $columnName, $value) {
        if ($type == "aes") {
            if ($value != null) {
                $query = $this->connection->prepare("UPDATE IGNORE setting
                                                        SET {$columnName} = AES_ENCRYPT(:{$columnName}, UNHEX(SHA2(secret_passphrase, 512)))
                                                    WHERE id = :id");

                $query->bindValue(":{$columnName}", $value);
                $query->bindValue(":id", 1);

                return $query->execute();
            }
        }
        else if ($type == "clear") {
            $query = $this->connection->prepare("UPDATE setting
                                                    SET {$columnName} = :{$columnName}
                                                WHERE id = :id");

            $query->bindValue(":{$columnName}", $value);
            $query->bindValue(":id", 1);

            return $query->execute();
        }

        return false;
    }
    
    // Slack
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
    
    // Line
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
    
    public function insertLinePushUserDatabase($elements) {
        $query = $this->connection->prepare("INSERT INTO setting_line_push_user (
                                                    push_name,
                                                    user_id,
                                                    email,
                                                    active
                                                )
                                                VALUES (
                                                    :pushName,
                                                    :userId,
                                                    :email,
                                                    :active
                                                );");
        
        $query->bindValue(":pushName", $elements[0]);
        $query->bindValue(":userId", $elements[1]);
        $query->bindValue(":email", $elements[2]);
        $query->bindValue(":active", $elements[3]);
        
        return $query->execute();
    }
    
    public function updateLinePushUserDatabase($elements) {
        if ($elements[0] != "" && $elements[1] != "" && $elements[2] != "") {
            $query = $this->connection->prepare("UPDATE setting_line_push_user
                                                    SET push_name = :pushName,
                                                        email = :email,
                                                        active = :active
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":pushName", $elements[0]);
            $query->bindValue(":email", $elements[2]);
            $query->bindValue(":active", $elements[3]);
            $query->bindValue(":userId", $elements[1]);
            
            return $query->execute();
        }
        else if ($elements[0] != "" && $elements[1] != "" && $elements[2] == "") {
            $query = $this->connection->prepare("UPDATE setting_line_push_user
                                                    SET push_name = :pushName
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":pushName", $elements[0]);
            $query->bindValue(":userId", $elements[1]);
            
            return $query->execute();
        }
        else if ($elements[0] == "" && $elements[1] != "" && $elements[2] == "") {
            $query = $this->connection->prepare("UPDATE setting_line_push_user
                                                    SET active = :active
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":active", $elements[3]);
            $query->bindValue(":userId", $elements[1]);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function deleteLinePushUserDatabase($pushName) {
        $query = $this->connection->prepare("DELETE FROM setting_line_push_user
                                                WHERE push_name = :pushName");
        
        $query->bindValue(":pushName", $pushName);
        
        return $query->execute();
    }
    
    // Language
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
    
    public function insertLanguageDatabase($type, $code, $date = "", $active = false) {
        if ($type == "text") {
            $query = $this->connection->prepare("INSERT INTO language (code, date, active)
                                                    VALUES (:code, :date, :active)");
            
            $query->bindValue(":code", $code);
            $query->bindValue(":date", $date);
            $query->bindValue(":active", $active);
            
            return $query->execute();
        }
        else if ($type == "page") {
            if (is_string($code) == true && strlen($code) == 2 && ctype_alpha($code) == true) {
                $query = $this->connection->prepare("ALTER TABLE page_title ADD $code VARCHAR(255) DEFAULT '';
                                                        ALTER TABLE page_argument ADD $code LONGTEXT;
                                                        ALTER TABLE page_menu_name ADD $code VARCHAR(255) NOT NULL DEFAULT '-';");
                
                return $query->execute();
            }
        }
        
        return false;
    }
    
    public function updateLanguageDatabase($date, $active, $code) {
        $query = $this->connection->prepare("UPDATE language
                                                SET date = :date,
                                                    active = :active
                                                WHERE code = :code");
        
        $query->bindValue(":date", $date);
        $query->bindValue(":active", $active);
        $query->bindValue(":code", $code);
        
        return $query->execute();
    }
    
    public function deleteLanguageDatabase($type, $code) {
        if ($type == "text") {
            $query = $this->connection->prepare("DELETE FROM language
                                                    WHERE code = :code
                                                    AND id > :id");
            
            $query->bindValue(":code", $code);
            $query->bindValue(":id", 1);
            
            return $query->execute();
        }
        else if ($type == "page") {
            if (is_string($code) == true && strlen($code) == 2 && ctype_alpha($code) == true) {
                $query = $this->connection->prepare("ALTER TABLE page_title DROP $code;
                                                        ALTER TABLE page_argument DROP $code;
                                                        ALTER TABLE page_menu_name DROP $code;");
                
                return $query->execute();
            }
        }
        
        return false;
    }
    
    // Page
    public function selectPageDatabase($language, $id, $draft = false) {
        if ($draft == false) {
            $query = $this->connection->prepare("SELECT page.*,
                                                    page_title.$language AS title,
                                                    page_argument.$language AS argument,
                                                    page_menu_name.$language AS menu_name
                                                    FROM page, page_title, page_argument, page_menu_name
                                                WHERE page.draft = :draft
                                                AND page.id = :id
                                                AND page_title.id = page.id
                                                AND page_argument.id = page.id
                                                AND page_menu_name.id = page.id
                                                ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
            
            $query->bindValue(":draft", 0);
        }
        else {
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
        }
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPageDatabase($language, $search = null, $draft = false) {
        if ($draft == false) {
            if ($search == null) {
                $query = $this->connection->prepare("SELECT page.*,
                                                        page_title.$language AS title,
                                                        page_argument.$language AS argument,
                                                        page_menu_name.$language AS menu_name
                                                    FROM page, page_title, page_argument, page_menu_name
                                                    WHERE page.draft = :draft
                                                    AND page_title.id = page.id
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
                                                    WHERE page.draft = :draft
                                                    AND page_title.id = page.id
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
            
            $query->bindValue(":draft", 0);
        }
        else {
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
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllParentPageDatabase($parent = 0, $draft = false) {
        if ($draft == false) {
            if ($parent != 0) {
                $query = $this->connection->prepare("SELECT * FROM page
                                                        WHERE draft = :draft
                                                        AND parent = :parent
                                                        ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
                
                $query->bindValue(":parent", $parent);
            }
            else
                $query = $this->connection->prepare("SELECT * FROM page
                                                        WHERE draft = :draft
                                                        AND parent is NULL
                                                        ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
            
            $query->bindValue(":draft", 0);
        }
        else {
            if ($parent != 0) {
                $query = $this->connection->prepare("SELECT * FROM page
                                                        WHERE parent = :parent
                                                        ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
                
                $query->bindValue(":parent", $parent);
            }
            else
                $query = $this->connection->prepare("SELECT * FROM page
                                                        WHERE parent is NULL
                                                        ORDER BY COALESCE(parent, rank_in_menu), rank_in_menu");
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllChildrenPageDatabase($parent, $draft = false) {
        if ($draft == false) {
            $query = $this->connection->prepare("SELECT * FROM page
                                                    WHERE draft = :draft
                                                    AND parent = :parent");
            
            $query->bindValue(":draft", 0);
        }
        else {
            $query = $this->connection->prepare("SELECT * FROM page
                                                    WHERE parent = :parent");
        }
        
        $query->bindValue(":parent", $parent);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function insertPageDatabase($language, $form) {
        $query = $this->connection->prepare("INSERT INTO page_title (
                                                    page_title.$language
                                                )
                                                VALUES (
                                                    :title
                                                );
                                                INSERT INTO page_argument (
                                                    page_argument.$language
                                                )
                                                VALUES (
                                                    :argument
                                                );
                                                INSERT INTO page_menu_name (
                                                    page_menu_name.$language
                                                )
                                                VALUES (
                                                    :menuName
                                                );");
        
        $query->bindValue(":title", $form->get("title")->getData());
        $query->bindValue(":argument", htmlentities($form->get("argument")->getData(), ENT_QUOTES, "UTF-8"));
        $query->bindValue(":menuName", $form->get("menuName")->getData());
        
        return $query->execute();
    }
    
    public function updatePageDatabase($form, $id) {
        $language = $form->get("language")->getData();
        
        $pageRow = $this->selectPageDatabase($language, $id, true);
        
        $alias = str_replace("_draft", "", $form->get("alias")->getData());
        $alias = $pageRow['draft'] > 0 ? "{$alias}_[draft]" : $alias;
        
        $query = $this->connection->prepare("UPDATE page, page_title, page_argument, page_menu_name
                                                SET page.alias = :alias,
                                                    page_title.$language = :title,
                                                    page_argument.$language = :argument,
                                                    page_menu_name.$language = :menuName
                                                WHERE page.id = :id
                                                AND page_title.id = :id
                                                AND page_argument.id = :id
                                                AND page_menu_name.id = :id");
        
        $query->bindValue(":alias", $alias);
        $query->bindValue(":title", $form->get("title")->getData());
        $query->bindValue(":argument", htmlentities($form->get("argument")->getData(), ENT_QUOTES, "UTF-8"));
        $query->bindValue(":menuName", $form->get("menuName")->getData());
        $query->bindValue(":id", $id);
        
        return $query->execute();
    }
    
    public function updateChildrenPageDatabase($id, $parentNew) {
        $query = $this->connection->prepare("UPDATE page
                                                SET parent = :parentNew
                                                WHERE parent = :id");
        
        $query->bindValue(":parentNew", $parentNew);
        $query->bindValue(":id", $id);
        
        return $query->execute();
    }
    
    public function updateRankInMenuPageDatabase($id, $rankMenuSort) {
        $rankMenuSortExplode = explode(",", $rankMenuSort);
        array_pop($rankMenuSortExplode);
        
        foreach ($rankMenuSortExplode as $key => $value) {
            if (empty($value) == true)
                $value = $id;

            $query = $this->connection->prepare("UPDATE page
                                                    SET rank_in_menu = :rankInMenu
                                                    WHERE id = :id");

            $query->bindValue(":rankInMenu", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
        
        return true;
    }
    
    public function deletePageDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM page WHERE id > :idExclude AND id = :id;
                                                    DELETE FROM page_title WHERE id > :idExclude AND id = :id;
                                                    DELETE FROM page_argument WHERE id > :idExclude AND id = :id;
                                                    DELETE FROM page_menu_name WHERE id > :idExclude AND id = :id;
                                                    DELETE FROM page_comment WHERE id > :idExclude AND id = :id;");
            
            $query->bindValue(":idExclude", 5);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM page WHERE id > :idExclude;
                                                    DELETE FROM page_title WHERE id > :idExclude;
                                                    DELETE FROM page_argument WHERE id > :idExclude;
                                                    DELETE FROM page_menu_name WHERE id > :idExclude;
                                                    DELETE FROM page_comment WHERE id > :idExclude;");
            
            $query->bindValue(":idExclude", 5);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function draftPageDatabase($type, $language, $user, $id, $form) {
        if ($type == "save") {
            $alias = "{$form->get("alias")->getData()}_[draft]";
            
            $pageRows = $this->selectAllPageDatabase($language, null, true);
            
            foreach ($pageRows as $key => $value) {
                if (isset($value['alias']) == true) {
                    if ($alias == $value['alias'])
                        return false;
                }
            }
            
            $query = $this->connection->prepare("INSERT INTO page (
                                                        alias,
                                                        parent,
                                                        controller_action,
                                                        role_user_id,
                                                        protected,
                                                        show_in_menu,
                                                        rank_in_menu,
                                                        comment,
                                                        only_parent,
                                                        only_link,
                                                        link,
                                                        user_create,
                                                        date_create,
                                                        user_modify,
                                                        date_modify,
                                                        meta_description,
                                                        meta_keywords,
                                                        meta_robots,
                                                        draft
                                                    )
                                                    SELECT 
                                                        :alias,
                                                        :parent,
                                                        :controllerAction,
                                                        :roleUserId,
                                                        :protected,
                                                        :showInMenu,
                                                        rank_in_menu,
                                                        :comment,
                                                        :onlyParent,
                                                        :onlyLink,
                                                        :link,
                                                        user_create,
                                                        date_create,
                                                        :userModify,
                                                        :dateModify,
                                                        :metaDescription,
                                                        :metaKeywords,
                                                        :metaRobots,
                                                        :id
                                                    FROM 
                                                        page
                                                    WHERE 
                                                        id = :id");
            
            $query->bindValue(":alias", $alias);
            $query->bindValue(":parent", $form->get("parent")->getData());
            $query->bindValue(":controllerAction", $form->get("controllerAction")->getData());
            $query->bindValue(":roleUserId", $form->get("roleUserId")->getData());
            $query->bindValue(":protected", $form->get("protected")->getData());
            $query->bindValue(":showInMenu", $form->get("showInMenu")->getData());
            $query->bindValue(":comment", $form->get("comment")->getData());
            $query->bindValue(":onlyParent", $form->get("onlyParent")->getData());
            $query->bindValue(":onlyLink", $form->get("onlyLink")->getData());
            $query->bindValue(":link", $form->get("link")->getData());
            $query->bindValue(":userModify", $user->getUsername());
            $query->bindValue(":dateModify", $this->helper->dateFormat());
            $query->bindValue(":metaDescription", $form->get("metaDescription")->getData());
            $query->bindValue(":metaKeywords", $form->get("metaKeywords")->getData());
            $query->bindValue(":metaRobots", $form->get("metaRobots")->getData());
            $query->bindValue(":id", $id);

            $query->execute();
            
            $languageRows = $this->selectAllLanguageDatabase();
            
            $title = "";
            $argument = "";
            $menuName = "";
            $code = "";
            
            foreach($languageRows as $key => $value) {
                $title .= "page_title.{$value['code']},";
                $argument .= "page_argument.{$value['code']},";
                $menuName .= "page_menu_name.{$value['code']},";
                $code .= "{$value['code']},";
            }
            
            $title = substr($title, 0, -1);
            $argument = substr($argument, 0, -1);
            $menuName = substr($menuName, 0, -1);
            $code = substr($code, 0, -1);
            
            $query = $this->connection->prepare("INSERT INTO page_title (
                                                        {$title}
                                                    )
                                                    SELECT
                                                        {$code}
                                                    FROM
                                                        page_title
                                                    WHERE
                                                        id = :id;
                                                    INSERT INTO page_argument (
                                                        {$argument}
                                                    )
                                                    SELECT
                                                        {$code}
                                                    FROM
                                                        page_argument
                                                    WHERE
                                                        id = :id;
                                                    INSERT INTO page_menu_name (
                                                        {$menuName}
                                                    )
                                                    SELECT
                                                        {$code}
                                                    FROM
                                                        page_menu_name
                                                    WHERE
                                                        id = :id;");

            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "publish") {
            $pageRow = $this->selectPageDatabase($language, $id, true);
            
            $alias = str_replace("_[draft]", "", $pageRow['alias']);
            
            if ($pageRow['draft'] > 0) {
                $query = $this->connection->prepare("DELETE FROM page WHERE id > :idExclude AND id = :id;
                                                        DELETE FROM page_title WHERE id > :idExclude AND id = :id;
                                                        DELETE FROM page_argument WHERE id > :idExclude AND id = :id;
                                                        DELETE FROM page_menu_name WHERE id > :idExclude AND id = :id;");

                $query->bindValue(":idExclude", 5);
                $query->bindValue(":id", $pageRow['draft']);

                $query->execute();
                
                $query = $this->connection->prepare("UPDATE page, page_title, page_argument, page_menu_name
                                                        SET page.id = :newId,
                                                            page.alias = :alias,
                                                            page.draft = :draft,
                                                            page_title.id = :newId,
                                                            page_argument.id = :newId,
                                                            page_menu_name.id = :newId
                                                        WHERE page.id = :id 
                                                        AND page_title.id = :id
                                                        AND page_argument.id = :id
                                                        AND page_menu_name.id = :id");

                $query->bindValue(":newId", $pageRow['draft']);
                $query->bindValue(":alias", $alias);
                $query->bindValue(":draft", 0);
                $query->bindValue(":id", $id);
            }
            else {
                $query = $this->connection->prepare("UPDATE page
                                                        SET alias = :alias,
                                                            draft = :draft
                                                        WHERE id = :id");
                
                $query->bindValue(":alias", $alias);
                $query->bindValue(":draft", 0);
                $query->bindValue(":id", $id);
            }
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Page comment
    public function selectPageCommentDatabase($type, $id, $username = "") {
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
    
    public function updatePageCommentDatabase($argument, $id, $user) {
        $query = $this->connection->prepare("UPDATE page_comment
                                                SET argument = :argument,
                                                    date_modify = :dateModify
                                                WHERE id = :id
                                                AND username = :username");
        
        $query->bindValue(":argument", base64_encode($argument));
        $query->bindValue(":dateModify", $this->helper->dateFormat());
        $query->bindValue(":id", $id);
        $query->bindValue(":username", $user->getUsername());
        
        return $query->execute();
    }
    
    // Module
    public function selectModuleDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM module
                                                WHERE id = :id
                                                ORDER BY COALESCE(position, rank_in_column), rank_in_column");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllModuleDatabase($id = 0, $position = null) {
        if ($id == 0 && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM module
                                                    WHERE position = :position
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
            
            $query->bindValue(":position", $position);
        }
        else if ($id > 0 && $position != null) {
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
        else if ($id == 0 && $position == null)
            $query = $this->connection->prepare("SELECT * FROM module
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function updateModuleDatabase($templateColumn) {
        if ($templateColumn == 1) {
            $query = $this->connection->prepare("UPDATE module
                                                    SET position_tmp = :positionTmp,
                                                        position = :position
                                                    WHERE position_tmp = :position");
            
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "right");
            
            $query->execute();
            
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "left");
            
            return $query->execute();
        }
        else if ($templateColumn == 2 || $templateColumn == 3 || $templateColumn == 4) {
            $query = $this->connection->prepare("UPDATE module
                                                    SET position_tmp = :positionTmp,
                                                        position = :position
                                                    WHERE position = :positionTmp");
            
            if ($templateColumn == 2) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->connection->prepare("UPDATE module
                                                        SET position_tmp = :positionTmp,
                                                            position = :position
                                                        WHERE position_tmp = :position");
                
                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "left");
                
                return $query->execute();
            }
            else if ($templateColumn == 3) {
                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->connection->prepare("UPDATE module
                                                        SET position_tmp = :positionTmp,
                                                            position = :position
                                                        WHERE position_tmp = :position");
                
                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "right");
                
                return $query->execute();
            }
            else if ($templateColumn == 4) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");

                $query->execute();
                
                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
                
                return $query->execute();
            }
        }
        
        $this->updateRankInColumnModuleDatabase("left");
        $this->updateRankInColumnModuleDatabase("center");
        $this->updateRankInColumnModuleDatabase("right");
        
        return true;
    }
    
    public function updateRankInColumnModuleDatabase($input) {
        $elementsExplode = explode(",", $input);
        
        if ($elementsExplode == false) {
            $moduleRows = $this->selectAllModuleDatabase(0, $input);
            
            foreach($moduleRows as $key => $value) {
                $query = $this->connection->prepare("UPDATE module
                                                        SET rank_in_column = :rankInColumn
                                                        WHERE id = :id");
                
                $query->bindValue(":rankInColumn", $key + 1);
                $query->bindValue(":id", $value['id']);
                
                $query->execute();
            }
            
            return true;
        }
        else {
            array_pop($elementsExplode);
            
            foreach ($elementsExplode as $key => $value) {
                $query = $this->connection->prepare("UPDATE module
                                                        SET rank_in_column = :rankInColumn
                                                        WHERE id = :id");
                
                $query->bindValue(":rankInColumn", $key + 1);
                $query->bindValue(":id", $value);
                
                $query->execute();
            }
            
            return true;
        }
        
        return false;
    }
    
    public function deleteModuleDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM module
                                                    WHERE id > :idExclude
                                                    AND id = :id");
            
            $query->bindValue(":idExclude", 2);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM module
                                                    WHERE id > :idExclude");
            
            $query->bindValue(":idExclude", 2);
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Payment
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
    
    public function updatePaymentDatabase($type, $userId, $id = 0) {
        if ($type == "deleteOne") {
            $query = $this->connection->prepare("UPDATE payment
                                                    SET status_delete = :statusDelete
                                                    WHERE user_id = :userId
                                                    AND id = :id");
            
            $query->bindValue(":statusDelete", 1);
            $query->bindValue(":userId", $userId);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->connection->prepare("UPDATE payment
                                                    SET status_delete = :statusDelete
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":statusDelete", 1);
            $query->bindValue(":userId", $userId);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function deletePaymentDatabase($type, $userId = 0, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM payment
                                                    WHERE user_id = :userId
                                                    AND id = :id");
            
            $query->bindValue(":userId", $userId);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "oneUser") {
            $query = $this->connection->prepare("DELETE FROM payment
                                                    WHERE user_id > :idExclude
                                                    AND user_id = :userId");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":userId", $userId);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM payment
                                                    WHERE user_id = :userId");
            
            $query->bindValue(":userId", $userId);
            
            return $query->execute();
        }
        else if ($type == "allUser") {
            $query = $this->connection->prepare("DELETE FROM payment
                                                    WHERE user_id > :idExclude");
            
            $query->bindValue(":idExclude", 1);
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Microservice api
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
    
    // ApiBasic
    public function selectApiBasicDatabase($value, $onlyActive) {
        $settingRow = $this->selectSettingDatabase();
        
        if (is_numeric($value) == true) {
            if ($onlyActive == true) {
                $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                        FROM microservice_apiBasic
                                                    WHERE id = :id
                                                    AND active = :active
                                                    ORDER by name ASC");
                
                $query->bindValue(":active", 1);
            }
            else {
                $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                        FROM microservice_apiBasic
                                                    WHERE id = :id
                                                    ORDER by name ASC");
            }
            
            $query->bindValue(":id", $value);
            
            $query->execute();
            
            return $query->fetch();
        }
        else {
            if ($onlyActive == true) {
                $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                        FROM microservice_apiBasic
                                                    WHERE token_name = :tokenName
                                                    AND active = :active
                                                    ORDER by name ASC");
                
                $query->bindValue(":active", 1);
            }
            else {
                $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                        FROM microservice_apiBasic
                                                    WHERE token_name = :tokenName
                                                    ORDER by name ASC");
            }
            
            $query->bindValue(":tokenName", $value);
            
            $query->execute();
            
            return $query->fetch();
        }
        
        return false;
    }
    
    public function selectAllApiBasicDatabase($onlyActive) {
        $settingRow = $this->selectSettingDatabase();
        
        if ($onlyActive == true) {
            $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                    FROM microservice_apiBasic
                                                WHERE active = :active
                                                ORDER by name ASC");
            
            $query->bindValue(":active", 1);
            
            $query->execute();
            
            return $query->fetchAll();
        }
        else {
            $query = $this->connection->prepare("SELECT *, AES_DECRYPT(database_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS database_password_decrypt
                                                    FROM microservice_apiBasic
                                                ORDER by name ASC");
            
            $query->execute();
            
            return $query->fetchAll();
        }
        
        return false;
    }
    
    public function updateApiBasicDatabase($type, $id, $name, $value) {
        if ($type == "aes") {
            if ($value != null) {
                $settingRow = $this->selectSettingDatabase();
                
                $query = $this->connection->prepare("UPDATE IGNORE microservice_apiBasic
                                                            SET {$name} = AES_ENCRYPT(:{$name}, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                        WHERE id = :id");
                
                $query->bindValue(":{$name}", $value);
                $query->bindValue(":id", $id);
                
                return $query->execute();
            }
        }
        else if ($type == "clear") {
            $query = $this->connection->prepare("UPDATE microservice_apiBasic
                                                        SET {$name} = :{$name}
                                                    WHERE id = :id");
            
            $query->bindValue(":{$name}", $value);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        
        return false;
    }
    
    public function selectApiBasicRequestDatabase($apiId, $name) {
        $query = $this->connection->prepare("SELECT * FROM microservice_apiBasic_request
                                                WHERE api_id = :apiId
                                                AND name = :name
                                                AND date LIKE :date
                                                ORDER by name ASC");
        
        $query->bindValue(":apiId", $apiId);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", "%{$this->helper->dateFormat(null, false)}%");
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllApiBasicRequestDatabase($apiId, $name, $year, $month) {
        $query = $this->connection->prepare("SELECT * FROM microservice_apiBasic_request
                                                WHERE api_id = :apiId
                                                AND name = :name
                                                AND date LIKE :date
                                                ORDER by name ASC");
        
        $query->bindValue(":apiId", $apiId);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", "%{$year}-{$month}%");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function insertApiBasicRequestDatabase($apiId, $name) {
        $query = $this->connection->prepare("INSERT INTO microservice_apiBasic_request (
                                                    api_id,
                                                    name,
                                                    date,
                                                    ip
                                                )
                                                VALUES (
                                                    :apiId,
                                                    :name,
                                                    :date,
                                                    :ip
                                                );");
        
        $query->bindValue(":apiId", $apiId);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", $this->helper->dateFormat());
        $query->bindValue(":ip", $_SERVER['REMOTE_ADDR']);
        
        return $query->execute();
    }
    
    public function updateApiBasicRequestDatabase($apiId, $name, $count) {
        $query = $this->connection->prepare("UPDATE microservice_apiBasic_request
                                                SET count = :count
                                                WHERE api_id = :apiId
                                                AND name = :name
                                                AND date LIKE :date");
        
        $query->bindValue(":count", $count + 1);
        $query->bindValue(":apiId", $apiId);
        $query->bindValue(":name", $name);
        $query->bindValue(":date", "%{$this->helper->dateFormat(null, false)}%");
        
        return $query->execute();
    }
    
    public function deleteApiBasicRequestDatabase($apiId) {
        $query = $this->connection->prepare("DELETE FROM microservice_apiBasic_request
                                                WHERE api_id = :apiId");
        
        $query->bindValue(":apiId", $apiId);
        
        return $query->execute();
    }
    
    public function selectAllApiBasicRequestDetailDatabase($name, $dateStart, $dateEnd) {
        if ($dateStart != "" && $dateEnd == "") {
            $query = $this->connection->prepare("SELECT * FROM microservice_apiBasic_request_detail
                                                    WHERE name = :name AND DATE(date) >= :dateStart
                                                    ORDER by name ASC");
            
            $query->bindValue(":name", $name);
            $query->bindValue(":dateStart", $dateStart);
            
            $query->execute();
            
            return $query->fetchAll();
        }
        else if ($dateStart == "" && $dateEnd != "") {
            $query = $this->connection->prepare("SELECT * FROM microservice_apiBasic_request_detail
                                                    WHERE name = :name AND DATE(date) <= :dateEnd
                                                    ORDER by name ASC");
            
            $query->bindValue(":name", $name);
            $query->bindValue(":dateEnd", $dateEnd);
            
            $query->execute();
            
            return $query->fetchAll();
        }
        else if ($dateStart != "" && $dateEnd != "") {
            $query = $this->connection->prepare("SELECT * FROM microservice_apiBasic_request_detail
                                                    WHERE name = :name AND DATE(date) >= :dateStart AND DATE(date) <= :dateEnd
                                                    ORDER by name ASC");
            
            $query->bindValue(":name", $name);
            $query->bindValue(":dateStart", $dateStart);
            $query->bindValue(":dateEnd", $dateEnd);
            
            $query->execute();
            
            return $query->fetchAll();
        }
        
        return false;
    }
    
    public function insertApiBasicRequestDetailDatabase($name, $json) {
        $query = $this->connection->prepare("INSERT INTO microservice_apiBasic_request_detail (
                                                name,
                                                date,
                                                data
                                            )
                                            VALUES (
                                                :name,
                                                :date,
                                                :data
                                            );");
        
        $query->bindValue(":name", $name);
        $query->bindValue(":date", $this->helper->dateFormat());
        $query->bindValue(":data", $json);
        
        return $query->execute();
    }
    
    // Microservice cron
    public function selectMicroserviceCronDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM microservice_cron
                                                WHERE id = :id
                                                ORDER by name ASC");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceCronDatabase() {
        $query = $this->connection->prepare("SELECT * FROM microservice_cron
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function updateMicroserviceCronDatabase($id) {
        $query = $this->connection->prepare("UPDATE microservice_cron
                                                SET last_execution = :last_execution
                                                WHERE id = :id");
        
        $query->bindValue(":last_execution", $this->helper->dateFormat());
        $query->bindValue(":id", $id);
        
        return $query->execute();
    }
    
    public function deleteMicroserviceCronDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM microservice_cron
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM microservice_cron");
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Microservice deploy
    public function selectMicroserviceDeployDatabase($type, $id) {
        if ($type == "normal") {
            $query = $this->connection->prepare("SELECT * FROM microservice_deploy
                                                    WHERE id = :id
                                                    ORDER by name ASC");

            $query->bindValue(":id", $id);

            $query->execute();

            return $query->fetch();
        }
        else if ($type == "aes") {
            $settingRow = $this->selectSettingDatabase();
            
            $query = $this->connection->prepare("SELECT AES_DECRYPT(ssh_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS ssh_password_decrypt,
                                                        AES_DECRYPT(key_private_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS key_private_password_decrypt,
                                                        AES_DECRYPT(git_clone_url_password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS git_clone_url_password_decrypt
                                                        FROM microservice_deploy
                                                    WHERE id = :id
                                                    ORDER by name ASC");

            $query->bindValue(":id", $id);
            
            $query->execute();
            
            return $query->fetch();
        }
        
        return false;
    }
    
    public function selectAllMicroserviceDeployDatabase() {
        $query = $this->connection->prepare("SELECT * FROM microservice_deploy
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function updateMicroserviceDeployDatabase($type, $id, $columnName, $value) {
        if ($type == "aes") {
            if ($value != null) {
                $settingRow = $this->selectSettingDatabase();
                
                $query = $this->connection->prepare("UPDATE IGNORE microservice_deploy
                                                            SET {$columnName} = AES_ENCRYPT(:{$columnName}, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                        WHERE id = :id");
                
                $query->bindValue(":{$columnName}", $value);
                $query->bindValue(":id", $id);

                return $query->execute();
            }
        }
        else if ($type == "clear") {
            $query = $this->connection->prepare("UPDATE microservice_deploy
                                                        SET {$columnName} = :{$columnName}
                                                    WHERE id = :id");

            $query->bindValue(":{$columnName}", $value);
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        
        return false;
    }
    
    public function deleteMicroserviceDeployDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM microservice_deploy
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM microservice_deploy");
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Microservice qunit
    public function selectMicroserviceQunitDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM microservice_qunit
                                                WHERE id = :id
                                                ORDER by name ASC");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllMicroserviceQunitDatabase() {
        $query = $this->connection->prepare("SELECT * FROM microservice_qunit
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function deleteMicroserviceQunitDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM microservice_qunit
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM microservice_qunit");
            
            return $query->execute();
        }
        
        return false;
    }
    
    // Other
    public function selectFirstRowDatabase($tableName) {
        $query = $this->connection->prepare("SELECT * FROM {$tableName}
                                                ORDER BY id ASC LIMIT 1");
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectLastRowDatabase($tableName) {
        $query = $this->connection->prepare("SELECT * FROM {$tableName}
                                                ORDER BY id DESC LIMIT 1");
        
        $query->execute();
        
        return $query->fetch();
    }
    
    // Functions private
    private function roleImplode($roleUserId) {
        $roleExplode = explode(",", $roleUserId);

        for ($a = 0; $a < count($roleExplode); $a ++) {
            if (intval($roleExplode[$a]) > 4)
                unset($roleExplode[$a]);
        }
        
        if (isset($roleExplode[0]) == false && empty($roleExplode[1]) == true)
            $roleExplode[1] = "1,";

        return implode(",", $roleExplode);
    }
}