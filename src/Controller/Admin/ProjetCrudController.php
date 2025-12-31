<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Validator\Constraints\File;

class ProjetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Projet::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du projet'),
            TextareaField::new('description', 'Description'),
            TextField::new('lienGithub', 'Lien GitHub')
                ->setHelp('URL du dépôt GitHub (optionnel)')
                ->setRequired(false),

            AssociationField::new('Language', 'Langages utilisés')
                ->setFormTypeOptions(['multiple' => true, 'by_reference' => false]),

            DateTimeField::new('createdAt', 'Date de création'),

            ImageField::new('file', 'Document du projet')
                ->setBasePath('uploads/projets')
                ->setUploadDir('public/uploads/projets')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setFileConstraints([
                    new File([
                        'maxSize' => '300M',
                        'mimeTypes' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-rar-compressed', 'application/octet-stream', 'text/plain', 'image/*'],
                        'mimeTypesMessage' => 'Format de fichier non autorisé.',
                    ]),
                ])
                ->setRequired(false)
                ->setHelp('PDF ou autre document lié au projet (optionnel)'),
        ];
    }
}
