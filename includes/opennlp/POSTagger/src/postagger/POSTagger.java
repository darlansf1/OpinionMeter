/*
 * Refer to https://opennlp.apache.org/documentation/1.6.0/manual/opennlp.html for more information on OPENNLP
 */
package postagger;
        
import java.io.File;
import java.io.IOException;
import opennlp.tools.cmdline.postag.POSModelLoader;
import opennlp.tools.postag.POSModel;
import opennlp.tools.postag.POSSample;
import opennlp.tools.postag.POSTaggerME;
import opennlp.tools.tokenize.WhitespaceTokenizer;

/**
 * @author Darlan Santana Farias - University of Sao Paulo, 2016
 */
public class POSTagger {
    private POSModel model;
    private POSTaggerME tagger;

    public POSTagger(String modelPath) {
        model = new POSModelLoader().load(new File(modelPath));
        tagger = new POSTaggerME(model);
    }

    public POSModel getModel() {
        return model;
    }

    public POSTaggerME getTagger() {
        return tagger;
    }
    
    public static void main(String[] args) throws IOException{
        String modelPath = "./../pos-models/pt-pos-maxent.bin";
        String[] words = WhitespaceTokenizer.INSTANCE.tokenize("Oi! Eu sou o Goku!");
        
        if(args.length > 1){
            modelPath = args[0];
        
            words = new String[args.length-1];
            for(int i = 1; i < args.length; i++)
                words[i-1] = args[i];
        }
        
        POSTagger posTagger = new POSTagger(modelPath);
        
        String[] tags = posTagger.getTagger().tag(words);
        
        for(int i = 0; i < words.length; i++){
            System.out.println(words[i]);
            System.out.println(tags[i]);
        }
    }
    
}
