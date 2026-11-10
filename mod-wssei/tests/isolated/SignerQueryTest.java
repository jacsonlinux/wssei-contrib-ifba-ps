import java.nio.file.*;
import java.util.*;
import org.apache.lucene.analysis.core.KeywordAnalyzer;
import org.apache.lucene.document.*;
import org.apache.lucene.index.*;
import org.apache.lucene.queryparser.classic.QueryParser;
import org.apache.lucene.search.*;
import org.apache.lucene.store.ByteBuffersDirectory;

// Synthetic index only; not proof of SEI indexing or effective authorization.
class SignerQueryTest {
  public static void main(String[] args) throws Exception {
    try (var dir = new ByteBuffersDirectory(); var analyzer = new KeywordAnalyzer()) {
      try (var writer = new IndexWriter(dir, new IndexWriterConfig(analyzer))) {
        String[] values = {"7", ";7;17;", ";17;", ";70;", ";7;", ";7;", ";7;", ";7;", null,
          ";2147483647;", ";107;", ";7;70;", ";17;7;", "7,17", "0007"};
        for (int i = 0; i < values.length; i++) {
          int id = i + 1;
          var doc = new Document();
          doc.add(new StoredField("id", id));
          doc.add(new NumericDocValuesField("id_sort", id));
          if (values[i] != null) doc.add(new StringField("id_assin", values[i], Field.Store.NO));
          doc.add(new StringField("id_serie", id == 5 ? "322" : "321", Field.Store.NO));
          doc.add(new StringField("id_tipo_proc", id == 6 ? "655" : "654", Field.Store.NO));
          doc.add(new StringField("tipo_aces_g", id == 7 || id == 8 ? "R" : "P", Field.Store.NO));
          doc.add(new StringField("id_uni_aces", id == 7 ? ";10;" : ";9;", Field.Store.NO));
          writer.addDocument(doc);
        }
      }
      try (var reader = DirectoryReader.open(dir)) {
        var searcher = new IndexSearcher(reader);
        var parser = new QueryParser("content", analyzer);
        parser.setAllowLeadingWildcard(true);
        var sort = new Sort(new SortField("id_sort", SortField.Type.INT));
        int checks = 0;
        for (var line : Files.readAllLines(Path.of(args[0]))) {
          var parts = line.split("\t", 3);
          var expected = new ArrayList<>(switch (parts[0]) {
            case "7" -> List.of(1, 2, 5, 6, 8, 12, 13, 14);
            case "17" -> List.of(2, 3, 13, 14);
            case "70" -> List.of(4, 12);
            case "2147483647" -> List.of(10);
            default -> throw new AssertionError("Unknown fixture");
          });
          if (parts[1].equals("combined")) expected.removeIf(id -> id == 5 || id == 6);
          var hits = searcher.search(parser.parse(parts[2]), 100, sort);
          var actual = new ArrayList<Integer>();
          for (var hit : hits.scoreDocs) actual.add(searcher.storedFields().document(hit.doc).getField("id").numericValue().intValue());
          if (!actual.equals(expected) || hits.totalHits.value != expected.size()) throw new AssertionError(parts[0] + ": " + actual);
          checks++;
        }
        System.out.println("OK: " + checks + " PHP-generated queries on synthetic Lucene index: complete IDs, multiple signers, AND and access condition");
      }
    }
  }
}
