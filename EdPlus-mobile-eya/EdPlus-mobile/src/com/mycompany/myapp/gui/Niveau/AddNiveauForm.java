/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.mycompany.myapp.gui.Niveau;

import com.codename1.ui.Button;
import com.codename1.ui.Command;
import com.codename1.ui.Dialog;
import com.codename1.ui.FontImage;
import com.codename1.ui.Form;
import com.codename1.ui.TextField;
import com.codename1.ui.events.ActionEvent;
import com.codename1.ui.events.ActionListener;
import com.codename1.ui.layouts.BoxLayout;
import com.codename1.ui.util.Resources;
import com.mycompany.myapp.gui.SideMenuBaseForm;
import entities.Niveau;
import services.ServiceNiveau;

/**
 *
 * @author bhk
 */
public class AddNiveauForm extends SideMenuBaseForm {
    Form current;

    public AddNiveauForm(Resources res ,Form previous) {

        setTitle("Ajouter un niveau");
        setLayout(BoxLayout.y());
        current = this;
        TextField tfNom = new TextField("", "Le nom du niveau");
        TextField tfniv = new TextField("", "Le niveau scolaire en chiffre");

        Button btnValider = new Button("Ajouter un niveau");

        btnValider.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent evt) {
                if ((tfNom.getText().length() == 0) || (tfniv.getText().length() == 0)) {
                    Dialog.show("Alert", "Please fill all the fields", new Command("OK"));
                } else {
                    Niveau NewNiv = new Niveau();
                    NewNiv.setNom(tfNom.getText());
                    NewNiv.setNivScolaire(Integer.parseInt(tfniv.getText()));

                    ServiceNiveau.getInstance().AddNiveau(NewNiv);
                  ListNiveauForm listProf = new ListNiveauForm(res);
                    listProf.show(); 

                }

            }
        });

        addAll(tfNom, tfniv, btnValider);
        getToolbar().addMaterialCommandToLeftBar("", FontImage.MATERIAL_ARROW_BACK, e -> previous.showBack());

    }

   

}
