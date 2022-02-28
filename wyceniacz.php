public function freeAction(Request $request)
		{
			$this->get('session')->remove('clientId');
		    $string_pass = '';
			if($request->query->get('email') !== NULL){
				$this->get('session')->remove('clientId');
				$em = $this->getDoctrine()->getManager();
				$user = $em->getRepository(UserPortal::class)->findOneByEmail($request->query->get('email'));
				 if ($user !== NULL)
				 {
						if ($user->getRoninlimit() === 0){
							$this->get('session')->remove('clientId');
							 return $this->render('wyceniacz/wyceniaczfreelimit.html.twig');
						}
						else {
							$this->get('session')->set('clientId', $user->getId());
							$this->get('session')->set('konto', 'uzytkownik');
						}
				 }
				 else {
					$generatepass = new Method();
					$string_pass = $generatepass->generateRandomString();
					$pass = $generatepass->makeHashPassword($request->query->get('email'), $string_pass);
					$clientfree = new UserPortal();

					$clientfree->setEmail($request->query->get('email'));
					$clientfree->setTel1(000000000);
					$clientfree->setTel2(0);
					$clientfree->setPass2($pass);
					$clientfree->setStatus(1);
					$clientfree->setNrgg(1);
					$clientfree->setKontakt(0);
					$clientfree->setOptions(2);
					$clientfree->setMarketing(1);
					$clientfree->setRoninlimit(1);
					$clientfree->setDateIn(date("y-m-d"));
					$clientfree->setToken(md5(uniqid(rand(), true)));
					$em = $this->getDoctrine()->getManager();
					$em->persist($clientfree);
					$em->flush();
					$user = $em->getRepository(UserPortal::class)->findOneByEmail($request->query->get('email'));
					$this->get('session')->set('clientId', $user->getId());
					$this->get('session')->set('konto', 'uzytkownik');
		 
				}
			}
			if($request->query->get('ami') != null)
			{
				if($request->query->get('obi') == 5) // tylko sprzedaz dla dzialek
					$request->query->set('akcja',1);
					$res1 = $this->wyceniaczPro($request->query->get('ami'),$request->query->get('obi'),$request->query->get('pow'),$request->query->get('akcja'),(!empty($request->query->get('lpokoi'))?$request->query->get('lpokoi'):null),(!empty($request->query->get('ad90'))?$request->query->get('ad90'):null),(!empty($request->query->get('ad120'))?$request->query->get('ad120'):null),$request->query->get('province1'));
			}
			if($request->query->get('ami2') != null && strlen($request->query->get('ami2')) > 2)
			{
			  $res2 = $this->wyceniaczPro($request->query->get('ami2'),$request->query->get('obi'),$request->query->get('pow'),$request->query->get('akcja'),(!empty($request->query->get('lpokoi'))?$request->query->get('lpokoi'):null),(!empty($request->query->get('2ad90'))?$request->query->get('2ad90'):null),(!empty($request->query->get('2ad120'))?$request->query->get('2ad120'):null),$request->query->get('province12'));
			}
      if($this->get('session')->get('clientId') !== NULL){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(UserPortal::class)->find($this->get('session')->get('clientId'));
        $roninlimit = $user->getRoninlimit();
      if(!empty($res1) && (!empty($res1['oferty_licznik_year2'])) && $res1['oferty_licznik_year2'] > 0 && $roninlimit > 0 )
      {
        $roninlimit2=$roninlimit-1;
        $user->setRoninlimit($roninlimit2);
        $em->flush();
      }
	  }
