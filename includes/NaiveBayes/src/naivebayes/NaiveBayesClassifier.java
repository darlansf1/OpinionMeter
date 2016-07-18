package naivebayes;

import java.io.BufferedReader;
import java.io.FileReader;
import weka.classifiers.bayes.NaiveBayes;
import weka.core.Instances;
import weka.core.converters.ConverterUtils.DataSource;
/**
 * @author Darlan Santana Farias
 */
public class NaiveBayesClassifier {
    public static void main(String[] args) {
        try {
            DataSource source = new DataSource("training_set.arff");
            Instances data = source.getDataSet();
            
            // setting class attribute if the data format does not provide this information
            if (data.classIndex() == -1)
              data.setClassIndex(data.numAttributes() - 1);
            
            NaiveBayes nb = new NaiveBayes(); 
            nb.buildClassifier(data);   // build and train classifier
            
            
            // load unlabeled data
            Instances unlabeled = new Instances(
                                    new BufferedReader(
                                      new FileReader("data.arff")));

            // set class attribute
            unlabeled.setClassIndex(unlabeled.numAttributes() - 1);

            // label instances
            for (int i = 0; i < unlabeled.numInstances(); i++) {
              double clsLabel = nb.classifyInstance(unlabeled.instance(i));
              System.out.println(unlabeled.classAttribute().value((int) clsLabel));
            }
        } catch (Exception ex) {
            ex.printStackTrace();
        }
    }
}
