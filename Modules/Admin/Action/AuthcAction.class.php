<?php
import('@.Common.Opadmin');
Class AuthcAction extends CommonAction{
	public function _initialize()
	{
		parent::_initialize();
		$authcategory=C('AUTH_CATEGORY');
		$this->assign('authcategory',$authcategory);
		S('done_list',null);	//清除done缓存
		R('Actlog/recorde');	//日志
	}
	public function group()
	{
		$g=M('Authgroup');
		$order['id']='asc';
		import('ORG.Util.Page');
		$count = $g->count();
		$pagenum=100;
		$Page = new Page($count,$pagenum);
		$nowPage = isset($_GET['p'])?$_GET['p']:0;
		$lists=$g->order($order)->page($nowPage.','.$Page->listRows)->select();
		foreach($lists as $k2 => $v2)
		{
			$lists[$k2]['rules']=explode(',',$v2['rules']);
		}
		
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('lists',$lists);
		//所有规则
		$rules=$this->getRules();
		foreach($rules as $k => $v)
		{
			$newrules[$v['id']]=$v['title'];
		}
		$this->assign('rules',$newrules);
		$this->display();
	}
/**
 * 添加分组
**/
	public function addgroup()
	{
		if($this->isPost())
		{
			$g=M('Authgroup');
			if($g->create())
			{
				foreach($g->rules as $k => $v)
				{
					$rules.=','.$v;
				}
				$g->rules=substr($rules,1);
				$lastid=$g->add();
				if($lastid)
					$this->success('分组添加成功！',U('group'));
				else
					$this->error('分组添加失败！');
			}
			else
			{
				$this->error($g->getError());
			}
		}
		else
		{
			$rules=$this->getRules();
			$this->assign('rules',$rules);
			$this->display();
		}
	}
/**
 * 修改分组
**/
	public function editgroup()
	{
		$g=M('Authgroup');
		if($this->isPost())
		{
			if($g->create())
			{
				foreach($g->rules as $k => $v)
				{
					$rules.=','.$v;
				}
				$g->rules=substr($rules,1);
				$lastid=$g->save();
				if($lastid)
					$this->success('分组修改成功！',U('group'));
				else
					$this->error('分组修改失败！');
			}
			else
			{
				$this->error($g->getError());
			}
		}
		else
		{
			//规则
			$rules=$this->getRules();
			$this->assign('rules',$rules);
			//
			$id=$this->_get('id');
			$where['id']=array('eq',$id);
			$data=$g->where($where)->find();
			$this->assign('data',$data);
			$this->display();
		}
	}
/**
 * 获取所有规则/用户组
 * 返回规则信息
 * status=1 开启
**/
	public function getRules($table='Authrule',$field='title,status,id,catid')
	{
		$r=M($table);
		if($table == 'Authrule')
		{
			$order['catid']='asc';
			$order['sort']='asc';
		}
		$order['id']='asc';
		$where['status']=array('eq',1);
		$data=$r->order($order)->field($field)->where($where)->select();
		return $data;
	}
/**
 * 所有规则
**/
	public function rules()
	{
		$r=M('Authrule');
		$order['catid']='asc';
		$order['sort']='asc';
		$order['id']='asc';
		import('ORG.Util.Pages2');
		if(I('search')){
            $where['catid']=array('eq',I('search'));
            $this->assign('search',I('search'));
        }
		$count = $r->where($where)->count();
		$Page = new Pages2($count,10);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$lists=$r->where($where)->order($order)->page($Page->nowPage.','.$Page->listRows)->select();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('lists',$lists);
		$this->display();
	}
/**
 * 添加规则
**/
	public function addrule()
	{
		if($this->isPost())
		{
			$r=D('Authrule');
			if(!$r->create())
			{
				$this->error($r->getError());
			}
			else
			{
				if(!$r->sort)
				{
					$m_where['catid']=array('eq',$r->catid);
					$max=$r->where($m_where)->max('sort');
					$r->sort=$max+1;
				}
				$r->nameback = strtolower($r->name);
				$lastid=$r->add();
				if($lastid)
				{
					$this->success('规则添加成功',U('rules'));
				}
				else
				{
					$this->error('规则添加失败');
				}
			}
		}
		else
		{
			$this->display();
		}
	}
/**
 * 修改规则
**/
	public function editrule()
	{
		$r=D('Authrule');
		if($this->isPost())
		{
			
			if(!$r->create())
			{
				$this->error($r->getError());
			}
			else
			{
				$r->nameback = strtolower($r->name);
				$lastid=$r->save();
				if($lastid)
				{
					$this->success('规则修改成功',U('rules',array('p'=>I('p'),'search'=>I('search'))));
				}
				else
				{
					$this->error('规则修改失败');
				}
			}
		}
		else
		{
			$where['id']=array('eq',$this->_get('id'));
			$data=$r->where($where)->find();
			$this->assign('data',$data);
			$this->assign('p',I('p','1'));
			$this->assign('search',I('search'));
			$this->display();
		}
	}
/**
 * 删除规则
**/
	public function delrule()
	{
		if($this->del($this->_get('id')))
			$this->success('删除成功！',U('rules',array('p'=>I('p'),'search'=>I('search'))));
		else
			$this->error('删除失败！');
	}
/**
 * 删除全部规则
**/
	public function delallrule()
	{
		$table="Authrule";
		$ids=explode(',', $this->_get('id'));
		foreach($ids as $key=>$vaule)
		{    
			if(!$this->del($vaule,$table))
			{
				$this->error('部分信息删除失败');
			}
		}
		$this->success('信息删除成功',U('rules'));
	}
/**
 * 修改规则
**/
	public function changeSort()
	{
		$table=$this->_post('table');
		$m=M($table);
		$where['id']=array('eq',$this->_post('id'));
		$f=$this->_post('f');
		$data[$f]=$this->_post('val');
		$count=$m->where($where)->data($data)->save();
		if($count)
		{
			$msg['success']=true;
			$msg['msg']=$this->_post('msg').'修改成功';
		}
		else
		{
			$msg['success']=false;
			$msg['msg']=$this->_post('msg').'修改失败';
		}
		echo json_encode($msg);
	}
/**
 * 删除用户组
**/
	public function delgroup()
	{
		if($this->del($this->_get('id'),'Authgroup'))
			$this->success('删除成功！',U('group'));
		else
			$this->error('删除失败！');
	}
/**
 * 删除全部用户组
**/
	public function delallgroup()
	{
		$table="Authgroup";
		$ids=explode(',', $this->_get('id'));
		foreach($ids as $key=>$vaule)
		{    
			if(!$this->del($vaule,$table))
			{
				$this->error('部分信息删除失败');
			}
		}
		$this->success('信息删除成功',U('group'));
	}
/**
 * 共公删除
 * $table
**/
	private function del($id,$table='Authrule')
	{
		$a=M($table);
		$where['id']=array('eq',$id);
		$count=$a->where($where)->delete();
		if($count)
			return true;
		else
			return false;
	}
/**
 * 修改分组开启属性
**/
	public function editattr()
	{
		$table=$this->_post('table');
		$m=M($table);
		$where['id']=array('eq',$this->_post('id'));
		$data['status']=$this->_post('status');
		$count=$m->where($where)->data($data)->save();
		if($count)
		{
			$msg['success']=true;
			$msg['msg']='状态已修改';
		}
		else
		{
			$msg['success']=false;
		}
		echo json_encode($msg);
	}
/**
 * 权限分配
 * uid 用户id
 * group_id 用户组id
**/
	public function fenpei()
	{
		$m=M('Authgroupaccess');
		$order='id asc';
		$where['uid']=array('neq','1');
		$list=$m->where($where)->order($order)->select();
		if($list)
		{
			foreach($list as $k => $v)
			{
				$list[$k]['username']=$this->getClassName($v['uid'],'Admin','username');
				$list[$k]['group_id']=explode(',',$v['group_id']);
			}
		}
		$this->assign('lists',$list);
		// 所有用户组
		$groups=$this->getRules('Authgroup','title,id');
		if($groups)
		{
			foreach($groups as $k2 => $v2)
			{
				$newgroups[$v2['id']]=$v2['title'];
			}
		}
		$this->assign('groups',$newgroups);
		$this->display();
	}
/**
 * 添加分配
 *
**/
	public function addfenpei()
	{
		if($this->isPost())
		{
			//查看会员是否已经分配用户组
			if($this->checkfenpei($this->_post('uid'),'uid','Authgroupaccess')) $this->error('会员已经分配角色');
			//
			$f=M('Authgroupaccess');
			if(!$f->create())
			{
				$this->error($f->getError());
			}
			else
			{
				/*foreach($f->group_id as $k => $v)
				{
					$group_id.=','.$v;
				}
				$f->group_id=substr($group_id,1);*/
				$lastid=$f->add();
				if($lastid)
					$this->success('分配成功',U('fenpei'));
				else
					$this->error('分配失败');
			}
		}
		else
		{
			//所有用户组
			$groups=$this->getRules('Authgroup','title,id');
			$this->assign('groups',$groups);
			//所有会员
			$m=M('Admin');
			$order='id asc';
			$where['id']=array('neq',1);
			$admin=$m->field('username,id')->where($where)->order($order)->select();
			$this->assign('admin',$admin);
			$this->display();	
		}
	}

/**
 * 删除分配
**/
	public function delfenpei()
	{
		if($this->del($this->_get('id'),'Authgroupaccess'))
			$this->success('删除成功！',U('fenpei'));
		else
			$this->error('删除失败！');
	}
/**
 * 删除全部分配
**/
	public function delallfenpei()
	{
		$table="Authgroupaccess";
		$ids=explode(',', $this->_get('id'));
		foreach($ids as $key=>$vaule)
		{    
			if(!$this->del($vaule,$table))
			{
				$this->error('部分信息删除失败');
			}
		}
		$this->success('信息删除成功',U('rules'));
	}
/**
 * 修改分配
 *
**/
	public function editfenpei()
	{
		$f=M('Authgroupaccess');
		if($this->isPost())
		{
			if(!$f->create())
			{
				$this->error($f->getError());
			}
			else
			{
				/*foreach($f->group_id as $k => $v)
				{
					$group_id.=','.$v;
				}
				$f->group_id=substr($group_id,1);*/
				$lastid=$f->save();
				if($lastid)
					$this->success('分配成功',U('fenpei'));
				else
					$this->error('分配失败');
			}
		}
		else
		{
			$where['id']=array('eq',$this->_get('id'));
			$data=$f->where($where)->find();
			$this->assign('data',$data);
			//所有用户组
			$groups=$this->getRules('Authgroup','title,id');
			$this->assign('groups',$groups);
			//所有会员
			$m=M('Admin');
			$order='id asc';
			// $admin=$m->field('username,id')->order($order)->select();
			$admin=$m->order($order)->getField('id,username');
			$this->assign('admin',$admin);
			$this->display();	
		}
	}
/**
 * 查看是否已经分配
 * $table 表名
 * $field 字段名
 * $id 传来的数值
**/
	private function checkfenpei($id,$field,$table='Authgroupaccess')
	{
		$m=M($table);
		$where[$field]=array('eq',$id);
		$count=$m->where($where)->count();
		if($count)
			return true;
		else
			return false;
	}
/**
 * 获取规则名称/会员名/用户组名......
 * $id 分类id
 * $table 表名
 * $typename 字段名
**/
	private function getClassName($id,$table='Authrule',$field='title')
	{
		$data=M($table)->field($field)->where('id='.$id)->find();
		return $data;
	}
}
?>