import redis.clients.jedis.Jedis;

import java.io.IOException;
import java.util.Iterator;
import java.util.Set;

public class Main {

    public native void cls();

    public static void main(String[] args) throws IOException {
	// write your code here
        Jedis jedis = new Jedis("127.0.0.1");
        while(true) {

            System.out.println("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n");

            System.out.print('\u000C');

            Set<String> names = jedis.keys("*");
            Iterator<String> it = names.iterator();

            while (it.hasNext()) {
                String s = it.next();
                String ans = jedis.get(s);
                if(ans.equals("answer!")) {
                  ans = ans + " - CORRECT ANSWER!!";
                } else {
                  ans = ans + " - WRONG!";
                }
                System.out.println(s + ": " + ans);
            }

            try {
                Thread.sleep(2500);
            } catch (Exception e) {
                System.out.println(e.getMessage());
                System.exit(0);
            }
        }

    }
}
