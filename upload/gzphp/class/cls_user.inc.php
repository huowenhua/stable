<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Tatfook Network Team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

class USER
{
	static function &instance()
	{
		static $u;
		
		if (empty($u))
		{
			$u = new session_user(); //使用SESSION来进行验证,客户端记录用户信息
		}
		
		return $u;
	}
	
	/// 获取一个会话变量
	static function get($key = false)
	{
		$u = &USER::instance();
		
		return $u->get_info($key);
	}
	
	///当前访问者的uid
	static function get_client_uid()
	{
		return (int)USER::get('__CLIENT_UID');
	}
	
	/// 当前访问者是否已登录,uid为空则未登录
	static function is_user_login()
	{
		$uid = USER::get('__CLIENT_UID');
		
		if ($uid > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/**
* 一个用户SESSION的类
*
* @author ibox
*
*/
class session_user
{
	// 保存客户端用户的信息
	public static $server_user_info = array();

	public function session_user()
	{
		//cookie清除则session也清除
		if ($_SESSION["client_info"] && ! $_COOKIE[G_COOKIE_PREFIX . "_user_login"])
		{
			unset($_SESSION["client_info"]);
		}
		
		//解掉COOKIE,然后进行验证
		if (! $_SESSION["client_info"] && $_COOKIE[G_COOKIE_PREFIX . "_user_login"])
		{
			//解码cookie
			$sso_user_login = H::decode_hash($_COOKIE[G_COOKIE_PREFIX . "_user_login"]);
			
			$account_obj = new account_class();
			
			if ($sso_user_login['user_name'] && $sso_user_login['password'] && $sso_user_login['uid'] && ($_SERVER['HTTP_USER_AGENT'] == $sso_user_login['UA']))
			{
				$rs = $account_obj->check_login($sso_user_login["user_name"], $sso_user_login["password"]);
				
				if ($rs)
				{
					$_SESSION["client_info"]["__CLIENT_UID"] = $sso_user_login["uid"];
					$_SESSION["client_info"]["__CLIENT_USER_NAME"] = $sso_user_login["user_name"];
					$_SESSION["client_info"]["__CLIENT_PASSWORD"] = $sso_user_login["password"];
					//$_SESSION["client_info"]["__CLIENT_SSO_KEY"] 	= strtoupper(md5(microtime() . rand(100000000, 999999999)));
					$_SESSION["client_info"]["__NEW_SESSION"] = TRUE;
					
					return true;
				}
			}
			
			return false;
		}
	}

	/**
	 * 返回信息
	 *
	 *  @param $key 字段名
	 */
	public function get_info($key)
	{
		return $_SESSION["client_info"][$key];
	}
}
