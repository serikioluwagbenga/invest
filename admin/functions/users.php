<?php
class users extends user
{

    function block_user($user_id, $what = "status") {
        if(!$this->validate_admin()) { return false; }
        $update = $this->update("users", ["$what"=>"blocked"], "ID = '$user_id'");
        if($update) {
            return $this->message("Blocked", "success", "json");
        }
    }

    // approve or reject deposit 
    function update_deposit_status() {
        $data = $this->checkmessage(["ID", "amount", "status", "reason_null"]);
        if(!is_array($data)) { return false; }
        $deposit = $this->getall("deposit", "ID = ? and status = ?", [$data['ID'], "pending"]);
        if(!is_array($deposit)) { return $this->message("Deposit is no more pending or not found.", "error", "json");}
        $id = $data['ID'];
        unset($data['ID']);
        $update = $this->update("deposit", $data, "ID = '$id'");
        if(!$update) { return $this->message("Unable to perfrom this action. Reload page and try again", "error", "json");  }
        if($data['status'] == "approved") {
            //  increase user balance
            $update = $this->increase_balance($data['amount'], $deposit['userID']);
            if(!$update) {
                $this->update("deposit", ["status"=>"pending"], "ID = '$id'");
                return $this->message("status not updated: Unable to increase balance", "error", "json");
            }    
        // activate pending investment if avilable
        $this->activate_pending($deposit['userID']);
        }
        // check and send email to user
        if($this->get_settings("send_email_to_user_deposit_".$data['status']) == "yes") {
            $user = $this->getall("users", "ID = ?", [$deposit['userID']]);
            if(!is_array($user)) { return $this->message("Deposit status updated. Email error: user not found", "error", "json");}
            $message = $this->get_email_template("deposit_".$data['status'])['template'];
            // ${amount} ${reason} ${website_url} 
            $message = $this->replace_word(['${first_name}'=>$user['first_name'], '${last_name}'=>$user['last_name'], '${amount}'=>$data['amount'], '${reason}'=>$data['reason'], '${website_url}'=>$this->get_settings("website_url")], $message);
            $sendmessage = $this->smtpmailer($user['email'], "Deposit ".$data['status'], $message);
            if(!$sendmessage) { return $this->message("Deposit status updated. Email error: SMTP issue contact the IT.", "success", "json"); }
        }   

        // return success message
        $return = [
                "message" => ["Success", "Deposit Status Updated", "success"],
            ];
            return json_encode($return);
    }

    function activate_pending($userID) {
        $invest = $this->getall("investment", "userID = ? and status  =?", [$userID, "pending"], fetch: "moredetails");
        if($invest->rowCount() == 0) {
            return true;
        }
        foreach($invest as $row) {
            $debit = $this->credit_debit($userID, $row['amount'], 'balance', 'debit');
            if(!$debit) {
                $this->message("Unable to activate this user pending investment of ".$this->money_format($row['amount']), "error");
                continue ;
            }
            $id = $row['ID'];
            $update = $this->update("investment", ["status"=>"active"],  "ID = '$id'");
            if($update) {
                $actInfo = ["userID" => $row['userID'],  "date_time" => date("Y-m-d H:i:s"),"action_name" => "Investment Activated", "description" => "An investment of ".$this->money_format($row['amount'], currency)." was activated.", "action_for"=>"investment", "action_for_ID"=>$row['ID']];
                $this->new_activity($actInfo);
            }
        }
    }

    function users_data() {
        // amount_invest number_invet profit_percent lost_percent profit_amount lost_amount trade_balance trade_bonus balance 
        $info = [];
        $user = $this->getall("users",  select: "COUNT(*)  as users_no, SUM(balance) as balance, SUM(trading_balance) as trading_balance, SUM(trade_bonus) as trade_bonus");
        $info['invest_no']  = $this->getall("investment", fetch: "");
        $deposit  = $this->getall("deposit", select: "COUNT(*) as deposit_no, SUM(amount) as total_deposit");
        $pending_deposit = $this->getall("deposit", "status = ?",["pending"], select: "COUNT(*) as pending_deposit_no, SUM(amount) as total_pending_deposit");
        $withdraw  = $this->getall("withdraw", select: "COUNT(*) as withdraw_no, SUM(amount) as total_withdraw");
        $info['pending_deposit_no']  = $this->getall("deposit", "status = ?", ["pending"], fetch: "");
        $invest = $this->getall("investment",  select: "SUM(amount) as total_investment_amount");
        $info['amount_invest'] = $invest['total_investment_amount'];
        $info['active_invest'] = $this->getall("investment", "status =? ", ["active"], fetch: "");
        $info['closed_invest'] = $this->getall("investment", "status =? ", ["closed"], fetch: "");
        $info['trade_no'] = $this->getall("trades", "status = ?", ["closed"], fetch: "");
        $trades_profit = $this->getall("trades", "percentage > ? and status = ?", [0, "closed"], "COUNT(*) AS trade_profit_no, SUM(intrest_amount) as profit_invest_total, SUM(percentage) as total_pecent_profit_invest");
        $trades_lost = $this->getall("trades", "percentage < ? and status = ?", [0, "closed"], "COUNT(*) AS trade_lost_no, SUM(intrest_amount) as lost_invest_total, SUM(percentage) as total_pecent_lost_invest");
        $info = array_merge($info, $trades_profit);
        $info = array_merge($info, $withdraw);
        $info = array_merge($info, $pending_deposit);
        $info = array_merge($info, $deposit);
        $info = array_merge($info, $trades_lost);
        $info = array_merge($info, $user);
        $info = array_merge($info, $invest);
        // var_dump($info);
        return $info;
    }

    function increase_balance($amount, $userID, $what = "balance", $oprator = "add") {
        $user = $this->getall("users", "ID = ?", [$userID], $what);
        if(!is_array($user)) { return false;}
        if($oprator == "add") {
            $amount = (float)$amount + (float)$user[$what];
        }
        if($oprator == "minus") {
            $amount = (float)$user[$what] - (float)$amount;
        }
        $update = $this->update("users", [$what=>$amount], "ID = '$userID'");
        if(!$update) {  return false;}
        return true;


    }
    function user_list($start, $limit = 1, $id = null, $type = "short")
    {
        $users = $this->fetchusers($start, $limit, $id);
        if ($users == "" || $users == null) {
            return null;
        }
        $info = "";
        foreach ($users as $user) {
            match ($type) {
                "short" => $info .= $this->short_user_table($user),
                "details" => $info .= $this->short_user_details($user),
            };
        }
        return $info;
    }

    function  short_user_table($user)
    {
        if(!is_array($user) && $user != "") {
            $user = $this->getall("users", "ID = ?", [$user]);
        }
        if(!is_array($user)) {
            return "<b class='text-danger'>User Not Found</b>";
        }
        return "<li>
            <a href='javascript:void(0)' onclick='display_content(this);' class='px-4 py-3 bg-hover-light-black d-flex align-items-center chat-user bg-light' id='chat_user_" . $user['ID'] . "'  data-user-id='" . $user['ID'] . "'>
                <span class='position-relative'>
                    <img src='" . $this->get_profile_icon_link($user['ID']) . "' alt='user-4' width='40' height='40' class='rounded-circle'>
                </span>
                <div class='ms-6 d-inline-block w-75'>
                    <h6 class='mb-1 fw-semibold chat-title' data-username='" . $this->get_full_name($user) . "'>" . $this->get_full_name($user) . " </h6>
                    <span class='fs-2 text-body-color d-block'>" . $user['email'] . "</span>
                </div>
            </a>
        </li>";
    }
    function  short_user_details($user)
    {
        if(!is_array($user) && $user != "") {
            $user = $this->getall("users", "ID = ?", [$user]);
        }
        if(!is_array($user)) {
            return "<b class='text-danger'>User Not Found</b>";
        }
        $profile_link =  $this->get_profile_icon_link($user['ID']);
        return "
        <div class='chat-list chat' id='content".$user['ID']."' data-user-id='" . $user['ID'] . "'>
           <div class='hstack align-items-start mb-7 pb-1 align-items-center justify-content-between'>
               <div class='d-flex align-items-center gap-3'>
                   <img src='$profile_link' alt='user4' width='72' height='72' class='rounded-circle' />
                   <div>
                       <h6 class='fw-semibold fs-4 mb-0'>" . $this->get_full_name($user) . " </h6>
                       <p class='mb-0'>Joined since:" . $user['date'] . " </p>
                   </div>
               </div>
           </div>
           <div class='row'>
               <div class='col-4 mb-7'>
                   <p class='mb-1 fs-2'>Phone number</p>
                   <h6 class='fw-semibold mb-0'>" . $user['phone_number'] . "</h6>
               </div>
               <div class='col-8 mb-7'>
                   <p class='mb-1 fs-2'>Email address</p>
                   <h6 class='fw-semibold mb-0'>" . $user['email'] . "</h6>
               </div>
               <div class='col-12 mb-9'>
                   <p class='mb-1 fs-2'>Address</p>
                   <h6 class='fw-semibold mb-0'>312, Imperical Arc, New western corner</h6>
               </div>
               <div class='col-4 mb-7'>
                   <p class='mb-1 fs-2'>City</p>
                   <h6 class='fw-semibold mb-0'>New York</h6>
               </div>
               <div class='col-8 mb-7'>
                   <p class='mb-1 fs-2'>Country</p>
                   <h6 class='fw-semibold mb-0'>United Stats</h6>
               </div>
           </div>
           <div class='border-bottom pb-7 mb-4'>
               <p class='mb-2 fs-2'>Notes</p>
               <p class='mb-3 text-dark'>
                   Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque bibendum
                   hendrerit lobortis. Nullam ut lacus eros. Sed at luctus urna, eu fermentum diam.
                   In et tristique mauris.
               </p>
               <p class='mb-0 text-dark'>Ut id ornare metus, sed auctor enim. Pellentesque nisi magna, laoreet a augue eget, tempor volutpat diam.</p>
           </div>
           <div class='d-flex align-items-center gap-2'>
               <button class='btn btn-primary fs-2'>Edit</button>
               <button class='btn btn-danger fs-2'>Delete</button>
           </div>
       </div>
           ";
    }
}
