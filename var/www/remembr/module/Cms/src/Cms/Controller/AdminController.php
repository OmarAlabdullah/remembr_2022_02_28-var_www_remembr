<?php

namespace Cms\Controller;

use Base\Controller\BaseController;

class AdminController extends BaseController
{

    public function indexAction()
    {
        $content = $this->getEm()->getRepository('Cms\Entity\CmsPage')->findBy(array('deleted' => false), array('slug' => 'asc'));

        return array(
            'content' => $content
        );
    }

    public function setRequest($req)
    {
        $this->request = $req;
    }

    public function createAction()
    {

        $newpage = new \Cms\Entity\CmsPage();
        $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
        $form = $annotationbuilder->createForm($newpage);

        $langs = $this->getLanguages();
        $form->get('lang')->setAttributes(array(
            'options' => $langs
        ));

        $form->bind($newpage);

        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setData(array_merge_recursive($request->getPost()->toArray()));

            if ($form->isValid())
            {
                $this->getEm()->persist($newpage);
                $this->getEm()->flush();
                return $this->redirect()->toUrl('/admin/cms');
            }
        }

        return array(
            'form' => $form,
        );
    }

    public function gotoAction()
    {
        $lang = $this->params()->fromQuery('lang');
        $slug = $this->params()->fromQuery('slug');
        $editpage = $this->getEm()->getRepository('Cms\Entity\CmsPage')->findOneBy(array('slug' => $slug, 'lang' => $lang, 'deleted' => false));
        
        if (!$editpage) {
            $editpage = new \Cms\Entity\CmsPage();
            $editpage->setSlug($slug);
            $editpage->setLang($lang);
            $editpage->setTitle('');
            $editpage->setText('');
            $this->getEm()->persist($editpage);
            $this->getEm()->flush();
        }
        
        
        return $this->redirect()->toUrl('/admin/cms/edit/' . $editpage->getId());
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id');

        if (!$id)
        {
            return $this->redirect()->toRoute('cms', array('controller' => 'admin', 'action' => 'index'));
        }

        $editpage = $this->getEm()->find('Cms\Entity\CmsPage', $id);
        $annotationbuilder = new \Zend\Form\Annotation\AnnotationBuilder();
        $form = $annotationbuilder->createForm($editpage);

        $langs = $this->getLanguages();
        $form->get('lang')->setAttributes(array(
            'options' => $langs
        ));
        $form->get('lang')->setValue($editpage->getLang());

        $form->bind($editpage);

        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setData($request->getPost()->toArray());

            if ($form->isValid())
            {
                $this->getEm()->flush();
                return $this->redirect()->toUrl('/admin/cms');
            }
        }

        return array(
            'form' => $form,
        );
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');

        if (!$id)
        {
            return $this->redirect()->toRoute('cms', array('controller' => 'admin', 'action' => 'index'));
        }

        $repository = $this->getEm()->getRepository('Cms\Entity\CmsPage');
        $page = $repository->find($id);

        $page->setDeleted(true);

        $this->getEm()->flush();

        return $this->redirect()->toUrl('/admin/cms');
    }

    protected function getLanguages()
    {
        $repository = $this->getEm()->getRepository('Cms\Entity\CmsLang');
        $langs = $repository->findAll();
        $langsArray = array();

        foreach ($langs as $lang)
        {
            $langsArray[$lang->getLang()] = $lang->getLang();
        }

        return $langsArray;
    }

}
